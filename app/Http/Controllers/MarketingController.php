<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyGoal;
use App\Models\User;
use App\Models\UsersMarketing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    // ─── helpers privados ──────────────────────────────────────────────────────

    /**
     * Estadísticas completas a partir de una colección de encuestas.
     * Calcula promedios separados (experiencia / atención) Y el combinado.
     */
    private function calcStats($surveys): array
    {
        $total = $surveys->count();

        return [
            'total'                   => $total,
            'muy_feliz'               => $surveys->where('experience_rating', 4)->count(),
            'feliz'                   => $surveys->where('experience_rating', 3)->count(),
            'insatisfecho'            => $surveys->where('experience_rating', 2)->count(),
            'muy_insatisfecho'        => $surveys->where('experience_rating', 1)->count(),
            // Promedios separados
            'average_experience'      => $total ? round($surveys->avg('experience_rating'), 2) : 0,
            'average_service'         => $total ? round($surveys->avg('service_quality_rating'), 2) : 0,
            // Promedio combinado: media de los dos ratings por encuesta
            'average_combined'        => $total
                ? round(
                    $surveys->avg(fn($s) => ($s->experience_rating + $s->service_quality_rating) / 2),
                    2
                )
                : 0,
        ];
    }

    /**
     * Mismos stats pero para una colección de IDs de user_id (útil para consultores + sedes).
     *
     * @param array $userIds IDs de users_marketing cuyas encuestas directas se cuentan.
     * @param array $sedeTagIds IDs de sede a incluir también vía Survey.sede_id (encuestas de
     *   Trimax General etiquetadas con esa sede).
     * @param bool $excludeTagged Si es true, excluye las encuestas ya etiquetadas con una sede
     *   (usado para Trimax General: esas ya se cuentan exclusivamente en la sede elegida).
     */
    private function calcStatsForIds(
        array $userIds,
        ?string $startDate = null,
        ?string $endDate = null,
        array $sedeTagIds = [],
        bool $excludeTagged = false
    ): array {
        $query = Survey::where(function ($q) use ($userIds, $sedeTagIds, $excludeTagged) {
            $q->whereIn('user_id', $userIds);
            if ($excludeTagged) {
                $q->whereNull('sede_id');
            } elseif (!empty($sedeTagIds)) {
                $q->orWhereIn('sede_id', $sedeTagIds);
            }
        });

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]);
        }

        return $this->calcStats($query->get());
    }

    /**
     * Rango lunes-domingo de la semana en curso, para el cálculo de
     * cumplimiento semanal por sede. (Sin respuesta aún de Marketing sobre
     * si el corte debe ser otro — lunes-domingo es el default documentado
     * en el spec, fácil de cambiar en un solo lugar si lo definen distinto.)
     */
    private function semanaActual(): array
    {
        return [
            Carbon::now('America/Lima')->startOfWeek(Carbon::MONDAY)->startOfDay(),
            Carbon::now('America/Lima')->endOfWeek(Carbon::SUNDAY)->endOfDay(),
        ];
    }

    /**
     * Stats "por sede" para el dashboard nuevo: meta semanal, encuestas
     * obtenidas esta semana, % de cumplimiento, avance día a día, y
     * promedios por pregunta. Solo considera encuestas de esquema nuevo
     * (con productos_rating) — las anteriores al rediseño no tienen los
     * campos nuevos y quedarían fuera de estas métricas por diseño.
     */
    private function calcularSedeStats()
    {
        [$inicioSemana, $finSemana] = $this->semanaActual();

        $sedes = UsersMarketing::where('role', 'sede')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $sedes->map(function ($sede) use ($inicioSemana, $finSemana) {
            $surveysSede = Survey::esquemaNuevo()
                ->where(fn($q) => $q->where('user_id', $sede->id)->orWhere('sede_id', $sede->id))
                ->get();

            $surveysSemana = $surveysSede->filter(
                fn($s) => $s->created_at->between($inicioSemana, $finSemana)
            );

            $metaVigente  = SurveyGoal::vigentePara($sede->id);
            $metaSemanal  = $metaVigente?->meta_semanal;
            $obtenidas    = $surveysSemana->count();
            $cumplimiento = $metaSemanal ? (int) round(($obtenidas / $metaSemanal) * 100) : null;

            $avanceDiario = collect(range(0, 6))->map(function ($i) use ($inicioSemana, $surveysSemana) {
                $dia = $inicioSemana->copy()->addDays($i);
                return [
                    'fecha' => $dia->toDateString(),
                    'label' => $dia->translatedFormat('D'),
                    'total' => $surveysSemana->filter(fn($s) => $s->created_at->isSameDay($dia))->count(),
                ];
            });

            return [
                'id'               => $sede->id,
                'name'             => $sede->name,
                'location'         => $sede->location,
                'meta_semanal'     => $metaSemanal,
                'obtenidas_semana' => $obtenidas,
                'cumplimiento_pct' => $cumplimiento,
                'avance_diario'    => $avanceDiario,
                'total_historico'  => $surveysSede->count(),
                'avg_experiencia'  => round($surveysSede->pluck('experience_rating')->filter()->avg() ?? 0, 2),
                'avg_sede'         => round($surveysSede->pluck('sede_rating')->filter()->avg() ?? 0, 2),
                'avg_consultor'    => round($surveysSede->pluck('consultor_rating')->filter()->avg() ?? 0, 2),
                'avg_productos'    => round($surveysSede->pluck('productos_rating')->filter()->avg() ?? 0, 2),
            ];
        })->values();
    }

    // ─── Dashboard ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Sin valores por defecto: si no se envían fechas, se usa TODA la data real.
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $userId    = $request->get('user_id');

        // Solo aplicamos el rango de fechas cuando se envían AMBAS fechas.
        $hasDateRange = $startDate && $endDate;

        // ── Encuestas (todas, o filtradas por rango si se pidió) ──
        $query = Survey::with(['userMarketing:id,name,role,location', 'selectedSede:id,name,role,location'])
            ->when($hasDateRange, fn($q) => $q->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]));

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $surveys = $query->get();

        // ── Stats generales ──
        $stats = $this->calcStats($surveys);

        // ── Tendencia diaria (para el gráfico de líneas) ──
        $dailyTrend = Survey::when($hasDateRange, fn($q) => $q->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->selectRaw('DATE(created_at) as date,
                            COUNT(*) as total,
                            ROUND(AVG(experience_rating), 2) as avg_experience,
                            ROUND(AVG(service_quality_rating), 2) as avg_service,
                            ROUND(AVG((experience_rating + service_quality_rating) / 2), 2) as avg_combined')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── Stats por usuario ──
        $userStats = UsersMarketing::whereIn('role', ['consultor', 'sede', 'trimax'])
            ->where('is_active', true)
            ->with(['sedes'])
            ->get()
            ->map(function ($user) use ($startDate, $endDate) {

                if ($user->isConsultor()) {
                    $sedeIds = $user->sedes->pluck('id')->toArray();
                    $allIds  = array_merge([$user->id], $sedeIds);
                    $s       = $this->calcStatsForIds($allIds, $startDate, $endDate, $sedeIds);

                    return array_merge($s, [
                        'id'          => $user->id,
                        'name'        => $user->name,
                        'role'        => $user->role,
                        'location'    => $user->location,
                        'sedes_count' => count($sedeIds),
                    ]);
                }

                if ($user->isSede()) {
                    // Sus encuestas directas + las de Trimax General etiquetadas con esta sede
                    $s = $this->calcStatsForIds([$user->id], $startDate, $endDate, [$user->id]);
                } else {
                    // Trimax General — solo las encuestas que el cliente dejó sin sede seleccionada
                    $s = $this->calcStatsForIds([$user->id], $startDate, $endDate, [], true);
                }

                return array_merge($s, [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'role'        => $user->role,
                    'location'    => $user->location,
                    'sedes_count' => 0,
                ]);
            })
            ->sortByDesc('average_combined')
            ->values();

        // ── Lista para filtro ──
        $users = UsersMarketing::whereIn('role', ['consultor', 'sede', 'trimax'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'location']);

        // ── Encuestas recientes (preview de las 50 más nuevas) ──
        $recentSurveys = Survey::with(['userMarketing:id,name,role,location', 'selectedSede:id,name,role,location'])
            ->when($hasDateRange, fn($q) => $q->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]))
            ->when($userId, fn($q, $id) => $q->where('user_id', $id))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $sedeStats = $this->calcularSedeStats();
        $sedesParaMeta = UsersMarketing::where('role', 'sede')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('marketing.dashboard.index', compact(
            'stats',
            'userStats',
            'users',
            'recentSurveys',
            'startDate',
            'endDate',
            'userId',
            'dailyTrend',
            'sedeStats',
            'sedesParaMeta'
        ));
    }

    // ─── Metas por sede (CRUD mínimo — solo crear, con vigente_desde = hoy) ────

    public function storeGoal(Request $request)
    {
        $validated = $request->validate([
            'sede_id'      => 'required|integer|exists:users_marketing,id',
            'meta_semanal' => 'required|integer|min:1|max:1000',
        ]);

        SurveyGoal::create([
            'sede_id'       => $validated['sede_id'],
            'meta_semanal'  => $validated['meta_semanal'],
            'vigente_desde' => now('America/Lima')->toDateString(),
            'created_by'    => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    // ─── Detalle de una encuesta (AJAX — modal) ────────────────────────────────

    public function showSurvey(Survey $survey)
    {
        $survey->load(['userMarketing', 'selectedSede']);
        $evaluado = $survey->display_entity;

        return response()->json([
            'success' => true,
            'survey'  => [
                'id'                    => $survey->id,
                'client_name'           => $survey->client_name ?: 'Anónimo',
                'experience_rating'     => $survey->experience_rating,
                'service_quality_rating' => $survey->service_quality_rating,
                'average_combined'      => round(($survey->experience_rating + $survey->service_quality_rating) / 2, 2),
                'comments'              => $survey->comments,
                'created_at'            => $survey->created_at->format('d/m/Y H:i'),
                'evaluado_name'         => $evaluado->name,
                'evaluado_role'         => $evaluado->role,
                'evaluado_location'     => $evaluado->location,
            ],
        ]);
    }

    // ─── Guardar encuesta pública ──────────────────────────────────────────────

    /**
     * Llamado desde el controlador de encuesta pública al guardar.
     * Extráelo aquí o llama a este método desde SurveyPublicController.
     */
    public static function dispatchAlertIfNeeded(Survey $survey, UsersMarketing $evaluado): void
    {
        // Disparar si alguna calificación respondida es ≤ 2. service_quality_rating
        // ya no se pregunta en encuestas nuevas (queda null) — no cuenta como "mala"
        // por sí sola, a diferencia de antes.
        $ratings = array_filter([
            $survey->experience_rating,
            $survey->sede_rating,
            $survey->consultor_rating,
            $survey->productos_rating,
        ], fn($r) => !is_null($r));

        $hayCalificacionBaja = collect($ratings)->contains(fn($r) => $r <= 2);

        if (!$hayCalificacionBaja) {
            return;
        }

        // Destinatarios: users con rol marketing o super_admin
        $recipients = User::where('is_active', true)
            ->get()
            ->filter(fn($u) => $u->isMarketing() || $u->isSuperAdmin());

        foreach ($recipients as $recipient) {
            $recipient->notify(new \App\Notifications\SurveyAlertNotification($survey, $evaluado));
        }
    }

    // ─── API stats (AJAX) ──────────────────────────────────────────────────────

    public function getStats(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));
        $userId    = $request->get('user_id');

        $query = Survey::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $stats = $this->calcStats($query->get());

        return response()->json(['success' => true, 'data' => $stats]);
    }

    // ─── AJAX: Todas las encuestas con paginación y filtros ───────────────────

    public function encuestasAjax(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $userId    = $request->get('user_id');
        $rating    = $request->get('rating');

        $query = Survey::with(['userMarketing:id,name,role,location', 'selectedSede:id,name,role,location'])
            // Solo filtrar por fecha si se pasan ambos valores
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            )
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($rating, fn($q) => $q->where('experience_rating', $rating))
            ->orderBy('created_at', 'desc'); // más recientes primero

        $paginated = $query->paginate(20);

        $data = $paginated->getCollection()->map(fn($sv) => [
            'id'                     => $sv->id,
            'date'                   => $sv->created_at->format('d/m/Y'),
            'time'                   => $sv->created_at->format('H:i'),
            'client_name'            => $sv->client_name,
            'evaluado_name'          => $sv->display_entity->name,
            'evaluado_role'          => $sv->display_entity->role,
            'evaluado_location'      => $sv->display_entity->location,
            'experience_rating'      => $sv->experience_rating,
            'service_quality_rating' => $sv->service_quality_rating,
            'comments'               => $sv->comments,
        ]);

        return response()->json([
            'data'          => $data,
            'total'         => $paginated->total(),
            'from'          => $paginated->firstItem() ?? 0,
            'to'            => $paginated->lastItem()  ?? 0,
            'current_page'  => $paginated->currentPage(),
            'last_page'     => $paginated->lastPage(),
            'prev_page_url' => $paginated->previousPageUrl(),
            'next_page_url' => $paginated->nextPageUrl(),
        ]);
    }
}

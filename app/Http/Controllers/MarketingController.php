<?php

namespace App\Http\Controllers;

use App\Exports\MarketingConsultoresExport;
use App\Exports\MarketingResumenSedesExport;
use App\Models\Survey;
use App\Models\SurveyGoal;
use App\Models\User;
use App\Models\UsersMarketing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MarketingController extends Controller
{
    // ─── helpers privados ──────────────────────────────────────────────────────

    /**
     * Estadísticas completas a partir de una colección de encuestas. Se
     * mantiene tal cual para el endpoint público /stats (getStats) — no se
     * usa en el dashboard, que ahora calcula su propio promedio consolidado
     * (ver Survey::promedioConsolidado) sobre las 5 preguntas de calificación
     * en vez de solo experiencia/atención.
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
            'average_experience'      => $total ? round($surveys->avg('experience_rating'), 2) : 0,
            'average_service'         => $total ? round($surveys->avg('service_quality_rating'), 2) : 0,
            'average_combined'        => $total
                ? round(
                    $surveys->avg(fn($s) => ($s->experience_rating + $s->service_quality_rating) / 2),
                    2
                )
                : 0,
        ];
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

    /** Rango del mes calendario en curso, para el cumplimiento mensual del tab Resumen. */
    private function mesActual(): array
    {
        return [
            Carbon::now('America/Lima')->startOfMonth()->startOfDay(),
            Carbon::now('America/Lima')->endOfMonth()->endOfDay(),
        ];
    }

    /** Etiquetas Lun-Dom de la semana en curso, para los encabezados de día del tab Resumen. */
    private function diasSemanaLabels(): array
    {
        [$inicioSemana] = $this->semanaActual();

        return collect(range(0, 6))
            ->map(fn($i) => $inicioSemana->copy()->addDays($i)->translatedFormat('D'))
            ->all();
    }

    /**
     * Stats por sede — eje central del dashboard (reestructurado 2026-08-12):
     * identifica las encuestas de cada sede, consolida su total, compara
     * contra la meta semanal y calcula el % de cumplimiento. Los consultores
     * ya no son una entidad de primer nivel — su desempeño se agrega como
     * desglose dentro de la sede a la que están asignados.
     * Solo considera encuestas de esquema nuevo (con tiempos_entrega_rating)
     * — las anteriores al rediseño no tienen las preguntas nuevas y quedan
     * fuera de estas métricas por diseño.
     */
    private function calcularSedeStats()
    {
        [$inicioSemana, $finSemana] = $this->semanaActual();
        [$inicioMes, $finMes]       = $this->mesActual();

        $sedes = UsersMarketing::where('role', 'sede')
            ->where('is_active', true)
            ->orderBy('name')
            ->with('consultores')
            ->get();

        return $sedes->map(function ($sede) use ($inicioSemana, $finSemana, $inicioMes, $finMes) {
            $surveysSede = Survey::esquemaNuevo()->paraEntidad($sede->id)->get();

            $surveysSemana = $surveysSede->filter(
                fn($s) => $s->created_at->between($inicioSemana, $finSemana)
            );
            $surveysMes = $surveysSede->filter(
                fn($s) => $s->created_at->between($inicioMes, $finMes)
            );

            $metaVigente  = SurveyGoal::vigentePara($sede->id);
            $metaSemanal  = $metaVigente?->meta_semanal;
            $obtenidas    = $surveysSemana->count();
            $cumplimiento = $metaSemanal ? (int) round(($obtenidas / $metaSemanal) * 100) : null;

            // No existe meta mensual real en el sistema (solo meta_semanal) — se
            // estima multiplicando por 4.33 (promedio de semanas/mes), a pedido
            // del usuario, ya que Marketing no ha definido una meta mensual propia.
            $obtenidasMes         = $surveysMes->count();
            $metaMensualEstimada  = $metaSemanal ? (int) round($metaSemanal * 4.33) : null;
            $cumplimientoMensual  = $metaMensualEstimada ? (int) round(($obtenidasMes / $metaMensualEstimada) * 100) : null;

            $avanceDiario = collect(range(0, 6))->map(function ($i) use ($inicioSemana, $surveysSemana) {
                $dia = $inicioSemana->copy()->addDays($i);
                return [
                    'fecha' => $dia->toDateString(),
                    'label' => $dia->translatedFormat('D'),
                    'total' => $surveysSemana->filter(fn($s) => $s->created_at->isSameDay($dia))->count(),
                ];
            });

            $consultores = $sede->consultores
                ->where('is_active', true)
                ->map(fn($c) => [
                    'id'             => $c->id,
                    'name'           => $c->name,
                    'total_surveys'  => $c->consultor_stats['total_surveys'],
                    'average_rating' => $c->consultor_stats['average_rating'],
                ])
                ->values();

            return [
                'id'               => $sede->id,
                'name'             => $sede->name,
                'location'         => $sede->location,
                'meta_semanal'     => $metaSemanal,
                'obtenidas_semana' => $obtenidas,
                'cumplimiento_pct' => $cumplimiento,
                'avance_diario'    => $avanceDiario,
                'total_historico'  => $surveysSede->count(),
                'obtenidas_mes'    => $obtenidasMes,
                'meta_mensual_estimada'    => $metaMensualEstimada,
                'cumplimiento_mensual_pct' => $cumplimientoMensual,
                'promedio'         => Survey::promedioConsolidado($surveysSede),
                'csat'             => Survey::csatConsolidado($surveysSede),
                'consultores'      => $consultores,
            ];
        })->values();
    }

    /**
     * Cómo se están calificando los consultores — para el tab Resumen. A
     * diferencia del desglose dentro de cada tarjeta de sede (donde un
     * consultor con más de una sede asignada aparece repetido), acá sale
     * una sola vez con todas sus sedes juntas.
     */
    private function calcConsultoresResumen()
    {
        return UsersMarketing::where('role', 'consultor')
            ->where('is_active', true)
            ->with('sedes:id,name')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $ratings = Survey::where('consultor_id', $c->id)
                    ->whereNotNull('consultor_rating')
                    ->pluck('consultor_rating');

                return [
                    'id'            => $c->id,
                    'name'          => $c->name,
                    'sedes'         => $c->sedes->pluck('name')->implode(', ') ?: '—',
                    'total_surveys' => $ratings->count(),
                    'promedio'      => $ratings->isEmpty() ? 0.0 : round($ratings->avg(), 2),
                    'csat'          => $ratings->isEmpty()
                        ? 0.0
                        : round($ratings->filter(fn($r) => $r >= 3)->count() / $ratings->count() * 100, 1),
                ];
            })
            ->sortByDesc('total_surveys')
            ->values();
    }

    /** Fila TOTAL del tab Resumen: suma de meta y de obtenidas entre todas las sedes, semanal y mensual. */
    private function calcResumenTotales($sedeStats): array
    {
        $totales = [
            'obtenidas_semana'      => $sedeStats->sum('obtenidas_semana'),
            'meta_semanal'          => $sedeStats->sum('meta_semanal') ?: null,
            'obtenidas_mes'         => $sedeStats->sum('obtenidas_mes'),
            'meta_mensual_estimada' => $sedeStats->sum('meta_mensual_estimada') ?: null,
        ];

        $totales['cumplimiento_pct'] = $totales['meta_semanal']
            ? (int) round($totales['obtenidas_semana'] / $totales['meta_semanal'] * 100)
            : null;
        $totales['cumplimiento_mensual_pct'] = $totales['meta_mensual_estimada']
            ? (int) round($totales['obtenidas_mes'] / $totales['meta_mensual_estimada'] * 100)
            : null;

        // Mismo shape que 'avance_diario' de cada sede ([{'total' => n}, ...]) para
        // poder reusar la misma lógica de columnas por día en la vista y el export.
        $totales['avance_diario'] = collect(range(0, 6))
            ->map(fn($i) => ['total' => $sedeStats->sum(fn($s) => $s['avance_diario'][$i]['total'] ?? 0)])
            ->all();

        return $totales;
    }

    // ─── Dashboard ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Sin valores por defecto: si no se envían fechas, se usa TODA la data real.
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        // Solo aplicamos el rango de fechas cuando se envían AMBAS fechas.
        $hasDateRange = $startDate && $endDate;

        $surveys = Survey::when($hasDateRange, fn($q) => $q->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate   . ' 23:59:59',
        ]))->get();

        // ── Stats generales (cabecera): total + promedio consolidado del rango ──
        $stats = [
            'total'    => $surveys->count(),
            'promedio' => Survey::promedioConsolidado($surveys),
            'csat'     => Survey::csatConsolidado($surveys),
        ];

        $sedeStats     = $this->calcularSedeStats();
        $sedesConMeta  = $sedeStats->whereNotNull('meta_semanal');
        $sedesCumplen  = $sedesConMeta->filter(fn($s) => $s['cumplimiento_pct'] >= 100)->count();
        $resumenTotales = $this->calcResumenTotales($sedeStats);

        $sedesParaMeta = UsersMarketing::where('role', 'sede')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $consultoresResumen = $this->calcConsultoresResumen();
        $diasSemanaLabels   = $this->diasSemanaLabels();

        // ── Encuestas recientes con calificación baja (para la pestaña Alertas) ──
        $recentLowRated = Survey::with(['userMarketing:id,name,role,location', 'selectedSede:id,name,role,location'])
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get()
            ->filter(fn($s) => collect([
                $s->experience_rating,
                $s->sede_rating,
                $s->consultor_rating,
                $s->tiempos_entrega_rating,
                $s->promociones_rating,
            ])->filter()->contains(fn($r) => $r <= 2))
            ->take(20)
            ->values();

        return view('marketing.dashboard.index', compact(
            'stats',
            'sedeStats',
            'sedesConMeta',
            'sedesCumplen',
            'sedesParaMeta',
            'consultoresResumen',
            'resumenTotales',
            'diasSemanaLabels',
            'recentLowRated',
            'startDate',
            'endDate'
        ));
    }

    // ─── Exportar Excel (tab Resumen) ─────────────────────────────────────────

    public function exportResumenSedes()
    {
        $sedeStats = $this->calcularSedeStats();
        $totales   = $this->calcResumenTotales($sedeStats);

        return Excel::download(
            new MarketingResumenSedesExport($sedeStats->toArray(), $totales, $this->diasSemanaLabels()),
            'marketing_resumen_sedes.xlsx'
        );
    }

    public function exportResumenConsultores()
    {
        return Excel::download(
            new MarketingConsultoresExport($this->calcConsultoresResumen()->toArray()),
            'marketing_calificacion_consultores.xlsx'
        );
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
        $survey->load(['userMarketing', 'selectedSede', 'consultor']);
        $evaluado = $survey->display_entity;

        $preguntas = collect([
            ['label' => 'Experiencia general', 'valor' => $survey->experience_rating, 'texto' => $survey->experience_rating_text],
            ['label' => 'Sede', 'valor' => $survey->sede_rating, 'texto' => $survey->sede_rating_text],
            ['label' => $survey->consultor ? "Consultor ({$survey->consultor->name})" : 'Consultor', 'valor' => $survey->consultor_rating, 'texto' => $survey->consultor_rating_text],
            ['label' => 'Tiempos de entrega', 'valor' => $survey->tiempos_entrega_rating, 'texto' => $survey->tiempos_entrega_rating_text],
            ['label' => 'Promociones', 'valor' => $survey->promociones_rating, 'texto' => $survey->promociones_rating_text],
        ])->filter(fn($p) => !is_null($p['valor']))->values();

        return response()->json([
            'success' => true,
            'survey'  => [
                'id'                => $survey->id,
                'client_name'       => $survey->client_name ?: 'Anónimo',
                'ruc'               => $survey->ruc,
                'preguntas'         => $preguntas,
                'promedio'          => Survey::promedioConsolidado([$survey]),
                'comments'          => $survey->comments,
                'created_at'        => $survey->created_at->format('d/m/Y H:i'),
                'evaluado_name'     => $evaluado->name,
                'evaluado_role'     => $evaluado->role,
                'evaluado_location' => $evaluado->location,
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
            $survey->tiempos_entrega_rating,
            $survey->promociones_rating,
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

    // ─── API stats (AJAX) — endpoint público, se mantiene tal cual ────────────

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
        $sedeId    = $request->get('sede_id');
        $rating    = $request->get('rating');

        $query = Survey::with(['userMarketing:id,name,role,location', 'selectedSede:id,name,role,location'])
            // Solo filtrar por fecha si se pasan ambos valores
            ->when(
                $startDate && $endDate,
                fn($q) =>
                $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            )
            ->when($sedeId, fn($q) => $q->paraEntidad($sedeId))
            ->when($rating, fn($q) => $q->where('experience_rating', $rating))
            ->orderBy('created_at', 'desc'); // más recientes primero

        $paginated = $query->paginate(20);

        $data = $paginated->getCollection()->map(fn($sv) => [
            'id'                => $sv->id,
            'date'              => $sv->created_at->format('d/m/Y'),
            'time'              => $sv->created_at->format('H:i'),
            'client_name'       => $sv->client_name,
            'ruc'               => $sv->ruc,
            'evaluado_name'     => $sv->display_entity->name,
            'evaluado_role'     => $sv->display_entity->role,
            'evaluado_location' => $sv->display_entity->location,
            'experience_rating' => $sv->experience_rating,
            'promedio'          => Survey::promedioConsolidado([$sv]),
            'comments'          => $sv->comments,
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

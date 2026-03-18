<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\User;
use App\Models\UsersMarketing;
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
     */
    private function calcStatsForIds(array $ids, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Survey::whereIn('user_id', $ids);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]);
        }

        return $this->calcStats($query->get());
    }

    // ─── Dashboard ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));
        $userId    = $request->get('user_id');

        // ── Encuestas del rango ──
        $query = Survey::with('userMarketing:id,name,role,location')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $surveys = $query->get();

        // ── Stats generales ──
        $stats = $this->calcStats($surveys);

        // ── Tendencia diaria (para el gráfico de líneas) ──
        $dailyTrend = Survey::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
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
                    $s       = $this->calcStatsForIds($allIds, $startDate, $endDate);

                    return array_merge($s, [
                        'id'          => $user->id,
                        'name'        => $user->name,
                        'role'        => $user->role,
                        'location'    => $user->location,
                        'sedes_count' => count($sedeIds),
                    ]);
                }

                // sede / trimax — solo sus encuestas
                $s = $this->calcStatsForIds([$user->id], $startDate, $endDate);

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

        // ── Encuestas recientes ──
        $recentSurveys = Survey::with('userMarketing:id,name,role,location')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($userId, fn($q, $id) => $q->where('user_id', $id))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('marketing.dashboard.index', compact(
            'stats',
            'userStats',
            'users',
            'recentSurveys',
            'startDate',
            'endDate',
            'userId',
            'dailyTrend'
        ));
    }

    // ─── Detalle de una encuesta (AJAX — modal) ────────────────────────────────

    public function showSurvey(Survey $survey)
    {
        $survey->load('userMarketing');

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
                'evaluado_name'         => $survey->userMarketing->name,
                'evaluado_role'         => $survey->userMarketing->role,
                'evaluado_location'     => $survey->userMarketing->location,
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
        // Solo disparar si experiencia O atención es ≤ 2
        if ($survey->experience_rating > 2 && $survey->service_quality_rating > 2) {
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

        $query = Survey::with('userMarketing:id,name,role,location')
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
            'evaluado_name'          => $sv->userMarketing->name,
            'evaluado_role'          => $sv->userMarketing->role,
            'evaluado_location'      => $sv->userMarketing->location,
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

<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\UsersMarketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $userId = $request->get('user_id');

        // Construir query base
        $query = Survey::with('userMarketing:id,name,role,location')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $surveys = $query->get();

        // Estadísticas generales
        $stats = [
            'total' => $surveys->count(),
            'muy_feliz' => $surveys->where('experience_rating', 4)->count(),
            'feliz' => $surveys->where('experience_rating', 3)->count(),
            'insatisfecho' => $surveys->where('experience_rating', 2)->count(),
            'muy_insatisfecho' => $surveys->where('experience_rating', 1)->count(),
            'average_experience' => round($surveys->avg('experience_rating'), 2),
            'average_service' => round($surveys->avg('service_quality_rating'), 2),
        ];

        // 🆕 Estadísticas por usuario con sedes consolidadas
        $userStats = UsersMarketing::whereIn('role', ['consultor', 'sede'])
            ->where('is_active', true)
            ->with([
                'surveys' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                },
                'sedes.surveys' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            ])
            ->get()
            ->map(function ($user) use ($startDate, $endDate) {
                // Para consultores: incluir encuestas de sus sedes
                if ($user->isConsultor()) {
                    // Obtener IDs de sedes asignadas
                    $sedeIds = $user->sedes->pluck('id')->toArray();
                    $allIds = array_merge([$user->id], $sedeIds);

                    // Obtener todas las encuestas (propias + de sedes)
                    $allSurveys = Survey::whereIn('user_id', $allIds)
                        ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                        ->get();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role,
                        'location' => $user->location,
                        'sedes_count' => $sedeIds ? count($sedeIds) : 0,
                        'total_surveys' => $allSurveys->count(),
                        'avg_experience' => round($allSurveys->avg('experience_rating') ?? 0, 2),
                        'avg_service' => round($allSurveys->avg('service_quality_rating') ?? 0, 2),
                        'muy_feliz' => $allSurveys->where('experience_rating', 4)->count(),
                        'feliz' => $allSurveys->where('experience_rating', 3)->count(),
                        'insatisfecho' => $allSurveys->where('experience_rating', 2)->count(),
                        'muy_insatisfecho' => $allSurveys->where('experience_rating', 1)->count(),
                    ];
                } else {
                    // Para sedes: solo sus encuestas
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role,
                        'location' => $user->location,
                        'sedes_count' => 0,
                        'total_surveys' => $user->surveys->count(),
                        'avg_experience' => round($user->surveys->avg('experience_rating') ?? 0, 2),
                        'avg_service' => round($user->surveys->avg('service_quality_rating') ?? 0, 2),
                        'muy_feliz' => $user->surveys->where('experience_rating', 4)->count(),
                        'feliz' => $user->surveys->where('experience_rating', 3)->count(),
                        'insatisfecho' => $user->surveys->where('experience_rating', 2)->count(),
                        'muy_insatisfecho' => $user->surveys->where('experience_rating', 1)->count(),
                    ];
                }
            })
            ->sortByDesc('avg_experience'); // Ordenar por mejor promedio

        // Lista de usuarios para filtro
        $users = UsersMarketing::whereIn('role', ['consultor', 'sede'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'location']);

        // Encuestas recientes
        $recentSurveys = Survey::with('userMarketing:id,name,role,location')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('marketing.dashboard.index', compact('stats', 'userStats', 'users', 'recentSurveys', 'startDate', 'endDate', 'userId'));
    }

    /**
     * Obtener estadísticas vía API
     */
    public function getStats(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $userId = $request->get('user_id');

        $query = Survey::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $surveys = $query->get();

        $stats = [
            'total' => $surveys->count(),
            'muy_feliz' => $surveys->where('experience_rating', 4)->count(),
            'feliz' => $surveys->where('experience_rating', 3)->count(),
            'insatisfecho' => $surveys->where('experience_rating', 2)->count(),
            'muy_insatisfecho' => $surveys->where('experience_rating', 1)->count(),
            'average_experience' => round($surveys->avg('experience_rating'), 2),
            'average_service' => round($surveys->avg('service_quality_rating'), 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}

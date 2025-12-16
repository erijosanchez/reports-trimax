<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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
        $query = Survey::with('user:id,name,role,location')
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

        // Estadísticas por usuario (consultores/sedes)
        $userStats = User::whereIn('role', ['consultor', 'sede'])
            ->where('is_active', true)
            ->withCount(['surveys' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }])
            ->with(['surveys' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'location' => $user->location,
                    'total_surveys' => $user->surveys_count,
                    'avg_experience' => round($user->surveys->avg('experience_rating'), 2),
                    'avg_service' => round($user->surveys->avg('service_quality_rating'), 2),
                    'muy_feliz' => $user->surveys->where('experience_rating', 4)->count(),
                    'feliz' => $user->surveys->where('experience_rating', 3)->count(),
                    'insatisfecho' => $user->surveys->where('experience_rating', 2)->count(),
                    'muy_insatisfecho' => $user->surveys->where('experience_rating', 1)->count(),
                ];
            });

        // Lista de usuarios para filtro
        $users = User::whereIn('role', ['consultor', 'sede'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'location']);

        // Encuestas recientes
        $recentSurveys = Survey::with('user:id,name,role,location')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('dashboard.index', compact('stats', 'userStats', 'users', 'recentSurveys', 'startDate', 'endDate', 'userId'));
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

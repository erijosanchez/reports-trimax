<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UsersMarketing;
use Google\Service\Directory\Users;

class UserMarketingController extends Controller
{
    /**
     * Lista de usuarios con búsqueda y filtros
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');

        $query = UsersMarketing::query()
            ->whereIn('role', ['consultor', 'sede'])
            ->withCount('surveys')
            ->with('surveys');

        // Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($role) {
            $query->where('role', $role);
        }

        // Filtro por estado
        if ($status !== null) {
            $query->where('is_active', $status);
        }

        // ⭐ OBTENER CONTADORES TOTALES ANTES DE PAGINAR
        $totalSedes = UsersMarketing::where('role', 'sede')->count();
        $totalConsultores = UsersMarketing::where('role', 'consultor')->count();

        // Para total de encuestas
        $totalSurveys = UsersMarketing::whereIn('role', ['consultor', 'sede'])
            ->withCount('surveys')
            ->get()
            ->sum('surveys_count');

        // Paginar
        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calcular promedios para cada usuario y cargar relaciones
        $users->each(function ($user) {
            $user->average_rating = $user->surveys->avg('experience_rating') ?? 0;
            $user->total_surveys = $user->surveys->count();

            // Cargar sedes asignadas para consultores
            if ($user->role === 'consultor') {
                $user->load('sedes');
            }

            // Cargar consultores a cargo para sedes
            if ($user->role === 'sede') {
                $user->load('consultores');
            }
        });

        return view('marketing.users.index', compact(
            'users',
            'search',
            'role',
            'status',
            'totalSedes',
            'totalConsultores',
            'totalSurveys'
        ));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('marketing.users.create');
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|in:consultor,sede',
            'location' => 'required_if:role,sede|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user = UsersMarketing::create([
                'name' => $request->name,
                'role' => $request->role,
                'location' => $request->location,
                'unique_token' => Str::random(32),
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('marketing.users.show', $user->id)
                ->with('success', 'Usuario creado exitosamente. Link de encuesta generado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear usuario: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ver detalles de un usuario
     */
    public function show($id)
    {
        $user = UsersMarketing::with(['surveys', 'sedes.surveys'])->findOrFail($id);

        // Estadísticas del usuario
        if ($user->isConsultor()) {
            // Para consultores, incluir estadísticas de sedes asignadas
            $stats = [
                'total_surveys' => $user->total_surveys,
                'total_surveys_with_sedes' => $user->total_surveys_with_sedes,
                'average_rating' => $user->average_rating,
                'average_rating_with_sedes' => $user->average_rating_with_sedes,
                'muy_feliz' => $user->surveys->where('experience_rating', 4)->count(),
                'feliz' => $user->surveys->where('experience_rating', 3)->count(),
                'insatisfecho' => $user->surveys->where('experience_rating', 2)->count(),
                'muy_insatisfecho' => $user->surveys->where('experience_rating', 1)->count(),
            ];
        } else {
            // Para sedes, estadísticas normales
            $stats = [
                'total_surveys' => $user->surveys->count(),
                'average_rating' => round($user->surveys->avg('experience_rating'), 2),
                'muy_feliz' => $user->surveys->where('experience_rating', 4)->count(),
                'feliz' => $user->surveys->where('experience_rating', 3)->count(),
                'insatisfecho' => $user->surveys->where('experience_rating', 2)->count(),
                'muy_insatisfecho' => $user->surveys->where('experience_rating', 1)->count(),
            ];
        }

        // Encuestas recientes
        $recentSurveys = $user->surveys()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Tendencia (últimos 30 días)
        $trend = $user->surveys()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, AVG(experience_rating) as avg_rating')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('marketing.users.show', compact('user', 'stats', 'recentSurveys', 'trend'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $user = UsersMarketing::findOrFail($id);
        return view('marketing.users.edit', compact('user'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $user = UsersMarketing::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|in:consultor,sede',
            'location' => 'required_if:role,sede|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user->name = $request->name;
            $user->role = $request->role;
            $user->location = $request->location;
            $user->save();

            DB::commit();

            return redirect()
                ->route('marketing.users.show', $user->id)
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar usuario: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mostrar formulario para asignar sedes a un consultor
     */
    public function assignSedesForm($id)
    {
        $consultor = UsersMarketing::where('role', 'consultor')->findOrFail($id);

        $sedes = UsersMarketing::where('role', 'sede')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sedesAsignadas = $consultor->sedes()->pluck('users_marketing.id')->toArray();

        return view('marketing.users.assign-sedes', compact('consultor', 'sedes', 'sedesAsignadas'));
    }

    /**
     * Actualizar sedes asignadas
     */
    public function assignSedes(Request $request, $id)
    {
        $consultor = UsersMarketing::where('role', 'consultor')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'sedes' => 'nullable|array',
            'sedes.*' => 'exists:users_marketing,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Sincronizar sedes (elimina las viejas y agrega las nuevas)
            $consultor->sedes()->sync($request->sedes ?? []);

            DB::commit();

            return redirect()
                ->route('marketing.users.show', $consultor->id)
                ->with('success', 'Sedes asignadas correctamente al consultor');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al asignar sedes: ' . $e->getMessage());
        }
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleStatus($id)
    {
        $user = UsersMarketing::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activado' : 'desactivado';

        return back()->with('success', "Usuario {$status} exitosamente.");
    }

    /**
     * Regenerar token único
     */
    public function regenerateToken($id)
    {
        $user = UsersMarketing::findOrFail($id);
        $user->unique_token = Str::random(32);
        $user->save();

        return back()->with('success', 'Token regenerado exitosamente. Nuevo link de encuesta generado.');
    }

    /**
     * Eliminar usuario
     */
    public function destroy($id)
    {
        $user = UsersMarketing::findOrFail($id);

        try {
            DB::beginTransaction();

            // Eliminar encuestas asociadas
            $user->surveys()->delete();

            // Eliminar usuario
            $user->delete();

            DB::commit();

            return redirect()
                ->route('marketing.users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Generar QR Code
     */
    public function generateQR($id)
    {
        $user = UsersMarketing::findOrFail($id);

        // Generar URL del QR
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($user->survey_url);

        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'survey_url' => $user->survey_url
        ]);
    }

    /**
     * Exportar usuarios a CSV
     */
    public function export()
    {
        $users = UsersMarketing::whereIn('role', ['consultor', 'sede'])
            ->with('surveys')
            ->get();

        $filename = 'usuarios_trimax_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'ID',
                'Nombre',
                'Rol',
                'Ubicación',
                'Estado',
                'Total Encuestas',
                'Promedio',
                'Link Encuesta',
                'Fecha Creación'
            ]);

            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->role === 'consultor' ? 'Consultor' : 'Sede',
                    $user->location ?? 'N/A',
                    $user->is_active ? 'Activo' : 'Inactivo',
                    $user->surveys->count(),
                    round($user->surveys->avg('experience_rating'), 2),
                    $user->survey_url,
                    $user->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Vista previa de encuesta
     */
    public function preview($id)
    {
        $user = UsersMarketing::findOrFail($id);
        $token = $user->unique_token;

        return redirect()->route('survey.show', $token);
    }
}

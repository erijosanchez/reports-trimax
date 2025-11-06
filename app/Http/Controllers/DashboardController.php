<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dashboard;
use App\Models\User;
use App\Models\UserActivityLog;

class DashboardController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        
        $dashboards = Dashboard::active()
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->get();

        return view('dashboards.index', compact('dashboards'));
    }

    public function show($id)
    {
        $dashboard = Dashboard::findOrFail($id);
        $user = auth()->user();

        if (!$user->hasAccessToDashboard($id)) {
            abort(403, 'No tienes acceso a este dashboard');
        }

        // Log actividad
        UserActivityLog::log(
            $user->id,
            'view_dashboard',
            'Dashboard',
            $dashboard->id,
            "Visualizó dashboard: {$dashboard->name}"
        );

        $powerbiLink = $dashboard->decrypted_link;

        return view('dashboards.show', compact('dashboard', 'powerbiLink'));
    }

    public function create()
    {
        $this->authorize('create', Dashboard::class);
        return view('dashboards.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Dashboard::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'powerbi_link' => 'required|url',
        ]);

        $dashboard = Dashboard::create([
            'name' => $request->name,
            'description' => $request->description,
            'powerbi_link_encrypted' => encrypt($request->powerbi_link),
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        UserActivityLog::log(
            auth()->id(),
            'create_dashboard',
            'Dashboard',
            $dashboard->id,
            "Creó dashboard: {$dashboard->name}"
        );

        return redirect()->route('dashboards.index')
            ->with('success', 'Dashboard creado exitosamente');
    }
}

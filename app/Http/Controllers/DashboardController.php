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
            ->when(!$user->isAdmin() && !$user->isSuperAdmin(), function ($query) use ($user) {
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

        return redirect()->route('dashboards.edit', $dashboard->id)
            ->with('success', 'Dashboard creado. Ahora asigna usuarios.');
    }

    public function edit($id)
    {
        $dashboard = Dashboard::with('users')->findOrFail($id);

        $this->authorize('update', $dashboard);

        $allUsers = User::active()->orderBy('name')->get();
        $assignedUserIds = $dashboard->users->pluck('id')->toArray();

        return view('dashboards.edit', compact('dashboard', 'allUsers', 'assignedUserIds'));
    }

    public function update(Request $request, $id)
    {
        $dashboard = Dashboard::findOrFail($id);

        $this->authorize('update', $dashboard);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'powerbi_link' => 'required|url',
            'is_active' => 'boolean',
        ]);

        $dashboard->update([
            'name' => $request->name,
            'description' => $request->description,
            'powerbi_link_encrypted' => encrypt($request->powerbi_link),
            'is_active' => $request->boolean('is_active', true),
        ]);

        UserActivityLog::log(
            auth()->id(),
            'update_dashboard',
            'Dashboard',
            $dashboard->id,
            "Actualizó dashboard: {$dashboard->name}"
        );

        return back()->with('success', 'Dashboard actualizado exitosamente');
    }

    public function assignUsers(Request $request, $id)
    {
        $dashboard = Dashboard::findOrFail($id);

        $this->authorize('update', $dashboard);

        $request->validate([
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        // Sincronizar usuarios con permiso can_view = true
        $syncData = [];
        foreach ($request->input('users', []) as $userId) {
            $syncData[$userId] = ['can_view' => true];
        }

        $dashboard->users()->sync($syncData);

        UserActivityLog::log(
            auth()->id(),
            'assign_dashboard_users',
            'Dashboard',
            $dashboard->id,
            "Asignó " . count($syncData) . " usuarios al dashboard: {$dashboard->name}"
        );

        return back()->with('success', 'Usuarios asignados exitosamente');
    }

    public function destroy($id)
    {
        $dashboard = Dashboard::findOrFail($id);

        $this->authorize('delete', $dashboard);

        UserActivityLog::log(
            auth()->id(),
            'delete_dashboard',
            'Dashboard',
            $dashboard->id,
            "Eliminó dashboard: {$dashboard->name}"
        );

        $dashboard->delete();

        return redirect()->route('dashboards.index')
            ->with('success', 'Dashboard eliminado');
    }
}

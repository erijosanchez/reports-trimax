<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dashboard;
use App\Models\User;

class UserAccessController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', false)->with('dashboards')->get();
        $dashboards = Dashboard::all();
        return view('admin.access.index', compact('users', 'dashboards'));
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $dashboardIds = $request->input('dashboards', []);

        $user->dashboards()->sync($dashboardIds);

        return redirect()->route('admin.access.index')
            ->with('success', 'Permisos actualizados exitosamente');
    }
}

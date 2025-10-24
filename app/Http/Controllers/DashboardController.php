<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dashboard;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->is_admin) {
            $dashboards = Dashboard::active()->ordered()->get();
        } else {
            $dashboards = $user->dashboards()->active()->ordered()->get();
        }

        return view('dashboards.index', compact('dashboards'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $dashboard = Dashboard::findOrFail($id);

        if (!$user->hasAccessToDashboard($id)) {
            abort(403, 'No tienes acceso a este dashboard');
        }

        return view('dashboards.show', compact('dashboard'));
    }
}

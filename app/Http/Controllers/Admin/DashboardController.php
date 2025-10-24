<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dashboard;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboards = Dashboard::ordered()->get();
        return view('admin.dashboards.index', compact('dashboards'));
    }

    public function create()
    {
        return view('admin.dashboards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'embed_url' => 'required|url',
            'order_position' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        Dashboard::create($validated);

        return redirect()->route('admin.dashboards.index')
            ->with('success', 'Dashboard creado exitosamente');
    }

    public function edit($id)
    {
        $dashboard = Dashboard::findOrFail($id);
        return view('admin.dashboards.edit', compact('dashboard'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'embed_url' => 'required|url',
            'order_position' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        $dashboard = Dashboard::findOrFail($id);
        $dashboard->update($validated);

        return redirect()->route('admin.dashboards.index')
            ->with('success', 'Dashboard actualizado exitosamente');
    }

    public function destroy($id)
    {
        Dashboard::findOrFail($id)->delete();
        return redirect()->route('admin.dashboards.index')
            ->with('success', 'Dashboard eliminado exitosamente');
    }
}

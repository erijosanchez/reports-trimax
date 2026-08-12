<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\User;
use App\Notifications\AvisoEnviado;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

class AvisoController extends Controller
{
    public function index()
    {
        if (Gate::denies('enviar-avisos')) {
            abort(403, 'No tienes permiso para enviar avisos.');
        }

        $avisos = Aviso::with('creador:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $roles = Role::orderBy('name')->pluck('name');

        return view('avisos.index', compact('avisos', 'roles'));
    }

    public function store(Request $request)
    {
        if (Gate::denies('enviar-avisos')) {
            abort(403, 'No tienes permiso para enviar avisos.');
        }

        $validated = $request->validate([
            'titulo'       => 'required|string|max:150',
            'mensaje'      => 'required|string|max:2000',
            'todos'        => 'nullable|boolean',
            'roles'        => 'nullable|array',
            'roles.*'      => 'string|exists:roles,name',
        ]);

        $enviarATodos = $request->boolean('todos') || empty($validated['roles']);
        $roles = $enviarATodos ? null : array_values($validated['roles']);

        $destinatarios = User::where('is_active', true)
            ->when($roles, fn($q) => $q->role($roles))
            ->get();

        $aviso = Aviso::create([
            'titulo'              => $validated['titulo'],
            'mensaje'             => $validated['mensaje'],
            'roles'               => $roles,
            'user_id'             => Auth::id(),
            'total_destinatarios' => $destinatarios->count(),
        ]);

        Notification::send($destinatarios, new AvisoEnviado($aviso));

        ActivityLogService::log(
            Auth::id(),
            'enviar_aviso',
            'Aviso',
            $aviso->id,
            "Envió aviso \"{$aviso->titulo}\" a " . $aviso->destinatariosLabel() . " ({$destinatarios->count()} usuarios)"
        );

        return back()->with('success', "Aviso enviado a {$destinatarios->count()} usuario(s).");
    }

    public function destroy($id)
    {
        if (Gate::denies('enviar-avisos')) {
            abort(403, 'No tienes permiso para enviar avisos.');
        }

        $aviso = Aviso::findOrFail($id);

        if ($aviso->user_id !== Auth::id() && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Solo puedes eliminar tus propios avisos.');
        }

        $aviso->delete();

        ActivityLogService::log(Auth::id(), 'eliminar_aviso', 'Aviso', $id, "Eliminó el registro del aviso \"{$aviso->titulo}\"");

        return back()->with('success', 'Aviso eliminado del historial.');
    }
}

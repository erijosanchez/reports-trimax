<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bandeja de notificaciones in-app (campanita del navbar). Lee de la tabla
 * estándar de notificaciones de Laravel (canal "database") — no reemplaza el
 * correo, es un canal adicional que además sigue funcionando para quien
 * apagó el correo (User::wantsEmailNotifications()).
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $notificaciones = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($n) => [
                'id'          => $n->id,
                'tipo'        => $n->data['tipo'] ?? null,
                'titulo'      => $n->data['titulo'] ?? 'Notificación',
                'mensaje'     => $n->data['mensaje'] ?? '',
                'url'         => $n->data['url'] ?? null,
                'enviado_por' => $n->data['enviado_por'] ?? null,
                'leida'       => !is_null($n->read_at),
                'fecha'       => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'success'       => true,
            'notificaciones' => $notificaciones,
            'no_leidas'     => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notificacion = Auth::user()->notifications()->where('id', $id)->first();

        if (!$notificacion) {
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);
        }

        $notificacion->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Services;

use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Registra una acción de un usuario autenticado en la bitácora de actividad.
     *
     * El registro está envuelto en try/catch: si falla la escritura del log
     * (p. ej. columna nueva aún no creada en BD) NO debe romper la operación
     * principal del usuario; se deja constancia en el log de Laravel.
     */
    public static function log(
        int $userId,
        string $action,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?string $description = null,
        ?int $responseStatus = null
    ): ?UserActivityLog {
        try {
            return UserActivityLog::create([
                'user_id'         => $userId,
                'session_id'      => self::getCurrentSessionId(),
                'action'          => $action,
                'resource_type'   => $resourceType,
                'resource_id'     => $resourceId,
                'description'     => $description,
                'ip_address'      => request()->ip(),
                'user_agent'      => request()->userAgent(),
                'request_method'  => request()->method(),
                'request_url'     => request()->fullUrl(),
                'response_status' => $responseStatus,
                'created_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('No se pudo registrar actividad de usuario', [
                'user_id' => $userId,
                'action'  => $action,
                'error'   => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected static function getCurrentSessionId(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $session = $user->sessions()
            ->where('session_id', session()->getId())
            ->where('is_online', true)
            ->first();

        return $session?->id;
    }

    public static function getRecentActivity(int $userId, int $limit = 10)
    {
        return UserActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getActivityByAction(string $action, int $hours = 24)
    {
        return UserActivityLog::where('action', $action)
            ->where('created_at', '>=', now()->subHours($hours))
            ->count();
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FailedLoginAttempt;
use App\Models\IpBlacklist;
use Illuminate\Support\Facades\Cache;


class TrackFailedLoginsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo procesar peticiones de login
        if ($request->is('login') && $request->isMethod('post')) {
            // Si el login falló (código 422 o redirect con errores)
            if ($response->status() === 422 || 
                ($response->isRedirect() && session()->has('errors'))) {
                
                $this->logFailedAttempt($request);
                $this->checkAndBlockIp($request);
            }
        }

        return $next($request);
    }

    protected function logFailedAttempt(Request $request): void
    {
        FailedLoginAttempt::create([
            'email' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
        ]);
    }

    /**
     * Verificar y bloquear IP si es necesario
     */
    protected function checkAndBlockIp(Request $request): void
    {
        $ip = $request->ip();
        $maxAttempts = config('auth.max_login_attempts', 5);
        $lockoutTime = config('auth.lockout_duration', 15); // minutos

        // Contar intentos en los últimos 15 minutos
        $recentAttempts = FailedLoginAttempt::where('ip_address', $ip)
            ->where('attempted_at', '>', now()->subMinutes($lockoutTime))
            ->count();

        if ($recentAttempts >= $maxAttempts) {
            // Bloquear IP temporalmente
            IpBlacklist::updateOrCreate(
                ['ip_address' => $ip],
                [
                    'reason' => "Demasiados intentos de login fallidos ({$recentAttempts} intentos)",
                    'blocked_until' => now()->addMinutes($lockoutTime),
                ]
            );

            // Notificar a administradores (opcional)
            \Log::warning('IP bloqueada automáticamente por intentos fallidos', [
                'ip' => $ip,
                'attempts' => $recentAttempts,
                'blocked_until' => now()->addMinutes($lockoutTime),
            ]);
        }
    }
}

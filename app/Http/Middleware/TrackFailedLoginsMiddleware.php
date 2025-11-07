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
   public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // ✅ SOLO trackear si es POST a /login Y hay errores
        if ($request->isMethod('post') && 
            $request->is('login') && 
            $response->getStatusCode() === 302 &&
            session()->has('errors')) {
            
            $this->logFailedAttempt($request);
            $this->checkAndBlockIp($request);
        }

        return $response;
    }

    protected function logFailedAttempt(Request $request)
    {
        FailedLoginAttempt::create([
            'email' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
        ]);
    }

    protected function checkAndBlockIp(Request $request)
    {
        $ip = $request->ip();
        $maxAttempts = config('security.login.max_attempts', 5);
        $lockoutTime = config('security.login.lockout_duration', 15);

        $recentAttempts = FailedLoginAttempt::recentAttemptsByIp($ip, $lockoutTime);

        if ($recentAttempts >= $maxAttempts) {
            IpBlacklist::blockIp(
                $ip, 
                "Demasiados intentos fallidos ({$recentAttempts})", 
                $lockoutTime
            );

            \Log::warning('IP bloqueada automáticamente', [
                'ip' => $ip,
                'attempts' => $recentAttempts,
            ]);
        }
    }
}

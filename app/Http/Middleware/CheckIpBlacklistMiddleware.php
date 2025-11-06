<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\IpBlacklist;
use Symfony\Component\HttpFoundation\Response;

class CheckIpBlacklistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $ip = $request->ip();

        $blacklisted = IpBlacklist::where('ip_address', $ip)
            ->where(function ($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })
            ->first();

        if ($blacklisted) {
            \Log::warning('Intento de acceso desde IP bloqueada', [
                'ip' => $ip,
                'reason' => $blacklisted->reason,
            ]);

            abort(403, 'Acceso denegado. Su IP ha sido bloqueada.');
        }

        return $next($request);
    }
}

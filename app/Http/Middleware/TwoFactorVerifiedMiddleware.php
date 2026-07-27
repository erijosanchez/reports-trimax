<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $this->requiere2fa($user) && !$request->routeIs('2fa.*')) {
            if (!$user->hasTwoFactorEnabled()) {
                return redirect()->route('2fa.setup');
            }

            if (!session('2fa_verified')) {
                return redirect()->route('2fa.verify');
            }
        }

        return $next($request);
    }

    /**
     * S4 (SEGURIDAD.md): 2FA obligatorio solo para quienes aprueban
     * movimientos de dinero — super_admin, admin y finanzas. El resto de
     * roles sigue sin verse afectado.
     */
    private function requiere2fa($user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas();
    }
}

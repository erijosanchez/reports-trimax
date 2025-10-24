<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class PreventBackAfterLogoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si el usuario no está autenticado y está intentando acceder a rutas protegidas
        if (!Auth::check() && $this->isProtectedRoute($request)) {
            return $this->handleUnauthenticatedAccess($request);
        }

        // Si el usuario está en el login, aplicar headers para prevenir cache
        if ($this->isLoginRoute($request)) {
            return $this->addNoCacheHeaders($response);
        }

        // Si el usuario está autenticado en rutas protegidas, también agregar headers
        if (Auth::check() && $this->isProtectedRoute($request)) {
            return $this->addNoCacheHeaders($response);
        }

        return $response;
    }

    /**
     * Manejar acceso no autenticado a rutas protegidas
     */
    private function handleUnauthenticatedAccess(Request $request): Response
    {
        // Incrementar contador de intentos de navegación (atrás/adelante)
        $attempts = Session::get('navigation_attempts', 0) + 1;
        Session::put('navigation_attempts', $attempts);

        // Si supera el límite de intentos, redirigir a Google
        if ($attempts >= 3) {
            Session::forget('navigation_attempts');
            Session::flush();

            return redirect()->away('https://www.google.com');
        }

        // Redirigir al login con mensaje
        if ($request->expectsJson()) {
            return response()->json([
                'attempts' => $attempts,
                'warning' => $attempts >= 2 ? 'Siguiente intento te redirigirá a Google.' : null
            ], 401);
        }

        return redirect()->route('login')
            ->with('attempts', $attempts)
            ->with('warning', $attempts >= 2 ? 'Siguiente intento te redirigirá a Google.' : null);
    }

    /**
     * Agregar headers para prevenir cache del navegador
     */
    private function addNoCacheHeaders(Response $response): Response
    {
        return $response->withHeaders([
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate, pre-check=0, post-check=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Clear-Site-Data' => '"cache", "storage"',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    /**
     * Verificar si es una ruta protegida
     */
    private function isProtectedRoute(Request $request): bool
    {
        // Rutas que requieren autenticación
        return $request->is('dashboard') || 
               $request->is('dashboard/*') ||
               $request->is('profile') ||
               $request->is('profile/*');
    }

    /**
     * Verificar si es la ruta de login
     */
    private function isLoginRoute(Request $request): bool
    {
        return $request->is('login') || $request->is('register');
    }
}

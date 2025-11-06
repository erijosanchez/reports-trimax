<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $session = $user->sessions()
                ->where('session_id', session()->getId())
                ->where('is_online', true)
                ->first();

            if ($session) {
                $session->updateActivity();
            }
        }

        return $next($request);
    }
}

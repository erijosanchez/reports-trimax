<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\FailedLoginAttempt;
use App\Models\IpBlacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserSession;
use App\Models\UserLocation;
use App\Models\UserActivityLog;
use App\Services\LocationService;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->withInput($request->only('email'));
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ])->withInput($request->only('email'));
        }

        // Login exitoso
        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        // Crear sesión
        $session = UserSession::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_online' => true,
            'last_activity' => now(),
            'login_at' => now(),
        ]);

        // Registrar ubicación
        LocationService::trackLocation(
            $user->id,
            $session->id,
            $request->ip()
        );

        // Log actividad
        UserActivityLog::log($user->id, 'login', 'User', $user->id, 'Usuario inició sesión');

        // Actualizar último login
        $user->updateLastLogin();

        return redirect()->intended(route('home'));
    }

    protected function handleFailedLogin(Request $request)
    {
        FailedLoginAttempt::create([
            'email' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
        ]);

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

            \Log::warning('IP bloqueada automáticamente por intentos fallidos', [
                'ip' => $ip,
                'attempts' => $recentAttempts,
                'email' => $request->input('email'),
            ]);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Cerrar sesión en BD
            $session = UserSession::where('user_id', $user->id)
                ->where('session_id', session()->getId())
                ->where('is_online', true)
                ->first();

            if ($session) {
                $session->closeSession();
            }

            // Log actividad
            UserActivityLog::log($user->id, 'logout', 'User', $user->id, 'Usuario cerró sesión');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

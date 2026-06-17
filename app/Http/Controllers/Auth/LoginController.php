<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\FailedLoginAttempt;
use App\Models\IpBlacklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\UserSession;
use App\Services\ActivityLogService;

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

        // Credenciales incorrectas (usuario inexistente o contraseña errónea).
        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->recordFailedLogin(
                $request,
                $user ? 'wrong_password' : 'unknown_user',
                $user
            );

            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->withInput($request->only('email'));
        }

        // Cuenta desactivada.
        if (!$user->is_active) {
            $this->recordFailedLogin($request, 'inactive_account', $user);

            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ])->withInput($request->only('email'));
        }

        // Login exitoso
        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        // Crear sesión
        UserSession::create([
            'user_id' => $user->id,
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_online' => true,
            'last_activity' => now(),
            'login_at' => now(),
        ]);

        // Log actividad
        ActivityLogService::log($user->id, 'login', 'User', $user->id, 'Usuario inició sesión');

        // Actualizar último login
        $user->updateLastLogin();

        return redirect()->intended(route('home'));
    }

    /**
     * Registra un intento de login fallido y, si supera el umbral, bloquea la IP.
     *
     * Toda la escritura está protegida con try/catch: un fallo al registrar el
     * intento nunca debe convertir un "credenciales incorrectas" en un error 500.
     *
     * @param  string  $reason  unknown_user | wrong_password | inactive_account
     */
    protected function recordFailedLogin(Request $request, string $reason, ?User $user = null): void
    {
        $ip = $request->ip();

        try {
            FailedLoginAttempt::create([
                'user_id'      => $user?->id,
                'email'        => $request->input('email'),
                'ip_address'   => $ip,
                'user_agent'   => $request->userAgent(),
                'reason'       => $reason,
                'attempted_at' => now(),
            ]);

            // Si conocemos al usuario, dejamos también constancia en su bitácora.
            if ($user) {
                ActivityLogService::log(
                    $user->id,
                    'login_failed',
                    'User',
                    $user->id,
                    "Intento de inicio de sesión fallido ({$reason}) desde IP {$ip}",
                    401
                );
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo registrar intento de login fallido', [
                'ip'    => $ip,
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);
        }

        $this->checkAndBlockIp($request, $user);
    }

    /**
     * Bloquea la IP automáticamente si supera el máximo de intentos en la ventana
     * de tiempo configurada. Respeta la whitelist (localhost / IPs de confianza).
     */
    protected function checkAndBlockIp(Request $request, ?User $user = null): void
    {
        $ip = $request->ip();

        if (IpBlacklist::isWhitelisted($ip)) {
            return;
        }

        $maxAttempts = config('security.login.max_attempts', 5);
        $lockoutTime = config('security.login.lockout_duration', 15);

        try {
            $recentAttempts = FailedLoginAttempt::recentAttemptsByIp($ip, $lockoutTime);

            if ($recentAttempts >= $maxAttempts && config('security.ip_blacklist.auto_block', true)) {
                IpBlacklist::blockIp(
                    $ip,
                    "Demasiados intentos fallidos ({$recentAttempts})",
                    config('security.ip_blacklist.auto_block_duration', $lockoutTime)
                );

                Log::warning('IP bloqueada automáticamente por intentos fallidos', [
                    'ip'       => $ip,
                    'attempts' => $recentAttempts,
                    'email'    => $request->input('email'),
                ]);

                if ($user) {
                    ActivityLogService::log(
                        $user->id,
                        'ip_blocked',
                        'IpBlacklist',
                        null,
                        "IP {$ip} bloqueada automáticamente tras {$recentAttempts} intentos fallidos",
                        403
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo evaluar/bloquear IP por intentos fallidos', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
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
            ActivityLogService::log($user->id, 'logout', 'User', $user->id, 'Usuario cerró sesión');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Exceptions\Google2FAException;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /** Cuántos códigos de recuperación se generan al habilitar 2FA. */
    private const CANTIDAD_CODIGOS_RECUPERACION = 8;

    public function show()
    {
        $user = auth()->user();

        if ($user->hasTwoFactorEnabled()) {
            return view('auth.two-factor-verify');
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        // QR generado en el servidor (SVG, sin extensiones ni servicios
        // externos) — antes se armaba con chart.googleapis.com, una API de
        // Google dada de baja hace años, y de paso el secreto viajaba a un
        // tercero para dibujar la imagen.
        $qrCodeSvg = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret,
            200
        );

        return view('auth.two-factor-setup', [
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
            'secret' => 'required',
        ]);

        $google2fa = new Google2FA();

        try {
            $valid = $google2fa->verifyKey($request->secret, $request->code);
        } catch (Google2FAException $e) {
            $valid = false;
        }

        if (!$valid) {
            $user = auth()->user();
            ActivityLogService::log($user->id, '2fa_enable_failed', 'User', $user->id, 'Código inválido al intentar habilitar 2FA', 422);

            return back()->withErrors(['code' => 'Código inválido']);
        }

        $user = auth()->user();
        $codigosEnClaro = $this->generarCodigosRecuperacion();

        $user->update([
            'two_factor_secret' => encrypt($request->secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $this->hashearCodigos($codigosEnClaro),
        ]);

        ActivityLogService::log($user->id, 'enable_2fa', 'User', $user->id, 'Habilitó autenticación en dos pasos');

        // Los códigos en claro solo existen en este momento — el modelo
        // guarda el hash, no se pueden volver a mostrar después. Van por
        // sesión flash a una vista dedicada, una sola vez.
        return redirect()->route('2fa.recovery-codes')
            ->with('codigos_recuperacion', $codigosEnClaro);
    }

    /** Muestra los códigos de recuperación una única vez, justo después de habilitar 2FA. */
    public function recoveryCodes()
    {
        $codigos = session('codigos_recuperacion');

        if (!$codigos) {
            return redirect()->route('home');
        }

        return view('auth.two-factor-recovery-codes', ['codigos' => $codigos]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        try {
            $valid = $google2fa->verifyKey(
                decrypt($user->two_factor_secret),
                $request->code
            );
        } catch (Google2FAException $e) {
            // Secreto corrupto o con formato inválido: no debe tumbar la
            // petición con un 500, se trata igual que un código incorrecto
            // y sigue probando contra los códigos de recuperación.
            $valid = false;
        }

        if ($valid) {
            ActivityLogService::log($user->id, '2fa_verified', 'User', $user->id, 'Verificación 2FA exitosa');
            session(['2fa_verified' => true]);

            return redirect()->route('home');
        }

        // Si el código de la app no matchea, probar contra los códigos de
        // recuperación (uso único: el que matchea se consume).
        if ($this->consumirCodigoRecuperacionSiValido($user, $request->code)) {
            ActivityLogService::log($user->id, '2fa_verified_recovery_code', 'User', $user->id, 'Verificación 2FA con código de recuperación (se consumió uno)');
            session(['2fa_verified' => true]);

            return redirect()->route('home')->with(
                'warning',
                'Ingresaste con un código de recuperación. Te quedan ' . count($this->codigosRecuperacionDe($user)) . ' — genera nuevos desde tu perfil cuando puedas.'
            );
        }

        ActivityLogService::log($user->id, '2fa_failed', 'User', $user->id, 'Código 2FA inválido al verificar sesión', 401);

        return back()->withErrors(['code' => 'Código inválido']);
    }

    public function disable(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        ActivityLogService::log($user->id, 'disable_2fa', 'User', $user->id, 'Deshabilitó autenticación en dos pasos');

        return back()->with('success', '2FA deshabilitado');
    }

    /** 8 códigos tipo XXXX-XXXX, en claro (solo existen antes de hashearlos). */
    private function generarCodigosRecuperacion(): array
    {
        return collect(range(1, self::CANTIDAD_CODIGOS_RECUPERACION))
            ->map(fn () => Str::upper(Str::random(4) . '-' . Str::random(4)))
            ->all();
    }

    private function hashearCodigos(array $codigosEnClaro): string
    {
        return json_encode(array_map(fn ($c) => Hash::make($c), $codigosEnClaro));
    }

    /** @return string[] Hashes de los códigos que le quedan al usuario. */
    private function codigosRecuperacionDe($user): array
    {
        return json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];
    }

    /** Si $codigo matchea uno de los hashes guardados, lo consume (uso único) y persiste. */
    private function consumirCodigoRecuperacionSiValido($user, string $codigo): bool
    {
        $hashes = $this->codigosRecuperacionDe($user);

        foreach ($hashes as $i => $hash) {
            if (Hash::check(trim($codigo), $hash)) {
                unset($hashes[$i]);
                $user->update(['two_factor_recovery_codes' => json_encode(array_values($hashes))]);

                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        if ($user->hasTwoFactorEnabled()) {
            return view('auth.two-factor-verify');
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.two-factor-setup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
            'secret' => 'required',
        ]);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($request->secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Código inválido']);
        }

        $user = auth()->user();
        $user->update([
            'two_factor_secret' => encrypt($request->secret),
            'two_factor_confirmed_at' => now(),
        ]);

        return redirect()->route('home')->with('success', '2FA habilitado exitosamente');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Código inválido']);
        }

        session(['2fa_verified' => true]);

        return redirect()->route('home');
    }

    public function disable(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return back()->with('success', '2FA deshabilitado');
    }
}

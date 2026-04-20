<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class FirmaUsuarioController extends Controller
{
    public function index()
    {
        return view('firma.index', ['usuario' => Auth::user()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'firma_data' => 'required|string',
        ]);

        $data = $request->firma_data;

        // Validar que sea un data URI base64 de imagen PNG/JPEG/GIF
        if (!preg_match('/^data:image\/(png|jpeg|jpg|gif);base64,/', $data)) {
            return back()->withErrors(['firma_data' => 'Formato de firma inválido. Solo PNG, JPG o GIF.']);
        }

        // Límite ~2.5 MB en base64 (para imágenes escaneadas)
        if (strlen($data) > 3500000) {
            return back()->withErrors(['firma_data' => 'La imagen es demasiado grande. Máximo 2 MB.']);
        }

        Auth::user()->update(['firma_imagen' => $data]);

        ActivityLogService::log(Auth::id(), 'save_firma', 'User', Auth::id(), 'Guardó su firma digital');

        return back()->with('success', 'Firma guardada correctamente.');
    }

    public function destroy()
    {
        Auth::user()->update(['firma_imagen' => null]);

        ActivityLogService::log(Auth::id(), 'delete_firma', 'User', Auth::id(), 'Eliminó su firma digital');

        return back()->with('success', 'Firma eliminada.');
    }
}

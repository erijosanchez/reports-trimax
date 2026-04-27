<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Motorizado;
use Illuminate\Support\Facades\Hash;

class AuthMotorizadoController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $motorizado = Motorizado::where('email', $data['email'])
            ->where('estado', 'activo')
            ->first();

        if (!$motorizado || !Hash::check($data['password'], $motorizado->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Revocar tokens anteriores
        $motorizado->tokens()->delete();

        $token = $motorizado->createToken('motorizado-app')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'motorizado' => [
                'id'      => $motorizado->id,
                'nombre'  => $motorizado->nombre,
                'sede'    => $motorizado->sede,
                'telefono'=> $motorizado->telefono,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function me(Request $request)
    {
        $motorizado = $request->user();
        return response()->json([
            'id'      => $motorizado->id,
            'nombre'  => $motorizado->nombre,
            'sede'    => $motorizado->sede,
            'telefono'=> $motorizado->telefono,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\RetiroOrden;
use Illuminate\Http\Request;

class RetiroOrdenController extends Controller
{
    private const RESPONSABLES = ['Said', 'Ruth', 'Juan L', 'Rafael'];

    public function index()
    {
        $user = auth()->user();

        if (!$user->puedeVerRetirosOrdenes()) {
            abort(403, 'No tienes permiso para acceder a Retiros de Órdenes.');
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $registros = RetiroOrden::with('creator')->latest()->get();
        } else {
            $registros = RetiroOrden::with('creator')
                ->where('sede', $user->sede)
                ->latest()
                ->get();
        }

        return view('retiros-ordenes.index', [
            'registros'    => $registros,
            'responsables' => self::RESPONSABLES,
            'sedUsuario'   => $user->sede,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sede'               => 'nullable|string|max:255',
            'numero_orden'       => 'nullable|string|max:255',
            'motivo'             => 'nullable|string|max:255',
            'nombre_responsable' => 'nullable|string|in:Said,Ruth,Juan L,Rafael',
            'observacion'        => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();

        $retiro = RetiroOrden::create([
            'sede'               => $request->sede ?: $user->sede,
            'numero_orden'       => $request->numero_orden,
            'motivo'             => $request->motivo,
            'nombre_responsable' => $request->nombre_responsable,
            'observacion'        => $request->observacion,
            'status'             => 'espera',
            'created_by'         => $user->id,
        ]);

        $retiro->load('creator');

        return response()->json([
            'success'  => true,
            'message'  => 'Orden de retiro registrada correctamente.',
            'registro' => $this->formatRegistro($retiro),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sede'               => 'nullable|string|max:255',
            'numero_orden'       => 'nullable|string|max:255',
            'motivo'             => 'nullable|string|max:255',
            'nombre_responsable' => 'nullable|string|in:Said,Ruth,Juan L,Rafael',
            'observacion'        => 'nullable|string|max:2000',
        ]);

        $retiro = RetiroOrden::findOrFail($id);
        $retiro->update($request->only(['sede', 'numero_orden', 'motivo', 'nombre_responsable', 'observacion']));

        return response()->json([
            'success' => true,
            'message' => 'Registro actualizado correctamente.',
        ]);
    }

    public function reasignar(Request $request, $id)
    {
        $request->validate([
            'nombre_responsable' => 'required|string|in:Said,Ruth,Juan L,Rafael',
        ]);

        $retiro = RetiroOrden::findOrFail($id);
        $retiro->update(['nombre_responsable' => $request->nombre_responsable]);

        return response()->json([
            'success' => true,
            'message' => 'Responsable reasignado correctamente.',
        ]);
    }

    public function atender($id)
    {
        $retiro = RetiroOrden::findOrFail($id);
        $retiro->update(['status' => 'atendido']);

        return response()->json([
            'success' => true,
            'message' => 'Orden marcada como atendida.',
        ]);
    }

    public function rechazar($id)
    {
        $retiro = RetiroOrden::findOrFail($id);
        $retiro->update(['status' => 'rechazado']);

        return response()->json([
            'success' => true,
            'message' => 'Orden marcada como rechazada.',
        ]);
    }

    public function destroy($id)
    {
        $retiro = RetiroOrden::findOrFail($id);
        $retiro->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro eliminado correctamente.',
        ]);
    }

    private function formatRegistro(RetiroOrden $r): array
    {
        return [
            'id'                 => $r->id,
            'sede'               => $r->sede,
            'numero_orden'       => $r->numero_orden,
            'motivo'             => $r->motivo,
            'nombre_responsable' => $r->nombre_responsable,
            'observacion'        => $r->observacion,
            'status'             => $r->status,
            'created_at'         => $r->created_at?->format('d/m/Y H:i'),
            'creator_name'       => $r->creator?->name,
        ];
    }
}

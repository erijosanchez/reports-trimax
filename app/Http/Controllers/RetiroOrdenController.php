<?php

namespace App\Http\Controllers;

use App\Models\RetiroOrden;
use App\Models\User;
use Illuminate\Http\Request;

class RetiroOrdenController extends Controller
{
    private const RESPONSABLES_IDS = [10, 23, 27, 67];

    private function responsables()
    {
        return User::whereIn('id', self::RESPONSABLES_IDS)->orderBy('name')->get(['id', 'name']);
    }

    public function index()
    {
        $user = auth()->user();

        if (!$user->puedeVerRetirosOrdenes()) {
            abort(403, 'No tienes permiso para acceder a Retiros de Órdenes.');
        }

        if ($user->isSuperAdmin() || $user->isAdmin() || in_array($user->id, self::RESPONSABLES_IDS)) {
            $registros = RetiroOrden::with('creator')->latest()->get();
        } else {
            $registros = RetiroOrden::with('creator')
                ->where('sede', $user->sede)
                ->latest()
                ->get();
        }

        $responsables   = $this->responsables();
        $responsableMap = $responsables->pluck('id', 'name')->all(); // ['Said Falconi' => 10, ...]

        return view('retiros-ordenes.index', [
            'registros'      => $registros,
            'responsables'   => $responsables,
            'responsableMap' => $responsableMap,
            'sedUsuario'     => $user->sede,
            'currentUserId'  => $user->id,
        ]);
    }

    public function store(Request $request)
    {
        $names = $this->responsables()->pluck('name')->all();

        $request->validate([
            'sede'               => 'nullable|string|max:255',
            'numero_orden'       => 'nullable|string|max:255',
            'motivo'             => 'nullable|string|max:255',
            'nombre_responsable' => 'nullable|string|in:' . implode(',', $names),
        ]);

        $user = auth()->user();

        $retiro = RetiroOrden::create([
            'sede'               => $request->sede ?: $user->sede,
            'numero_orden'       => $request->numero_orden,
            'motivo'             => $request->motivo,
            'nombre_responsable' => $request->nombre_responsable,
            'observacion'        => null,
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
        $names = $this->responsables()->pluck('name')->all();

        $request->validate([
            'sede'               => 'nullable|string|max:255',
            'numero_orden'       => 'nullable|string|max:255',
            'motivo'             => 'nullable|string|max:255',
            'nombre_responsable' => 'nullable|string|in:' . implode(',', $names),
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
        $names = $this->responsables()->pluck('name')->all();

        $request->validate([
            'nombre_responsable' => 'required|string|in:' . implode(',', $names),
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
        $user   = auth()->user();
        $retiro = RetiroOrden::findOrFail($id);

        $responsable = $this->responsables()->firstWhere('name', $retiro->nombre_responsable);
        if (!$responsable || $responsable->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el responsable asignado puede marcar esta orden como atendida.',
            ], 403);
        }

        $retiro->update(['status' => 'atendido']);

        return response()->json([
            'success' => true,
            'message' => 'Orden marcada como atendida.',
        ]);
    }

    public function rechazar($id)
    {
        $user   = auth()->user();
        $retiro = RetiroOrden::findOrFail($id);

        $responsable = $this->responsables()->firstWhere('name', $retiro->nombre_responsable);
        if (!$responsable || $responsable->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el responsable asignado puede marcar esta orden como rechazada.',
            ], 403);
        }

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

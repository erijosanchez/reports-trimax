<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherFactura;
use App\Notifications\VoucherAplicado;
use App\Notifications\VoucherEnviadoParaAplicar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    private const SILVIA_ID    = 62;
    private const MAX_SIZE_KB  = 20480;
    private const MIMES        = 'jpg,jpeg,png,gif,webp,xlsx,xls,csv,pdf';

    public function index()
    {
        $user     = auth()->user();
        $esSilvia = $user->id === self::SILVIA_ID;

        if (!$user->puedeVerVouchers()) {
            abort(403, 'No tienes permiso para acceder a Vouchers.');
        }

        if ($esSilvia || $user->isSuperAdmin() || $user->isAdmin()) {
            $vouchers = Voucher::with(['creator', 'aplicador', 'facturas'])->latest()->get();
        } else {
            $vouchers = Voucher::with(['creator', 'aplicador', 'facturas'])
                ->where('sede', $user->sede)
                ->latest()
                ->get();
        }

        return view('vouchers.index', [
            'vouchers'   => $vouchers,
            'esSilvia'   => $esSilvia,
            'sedUsuario' => $user->sede,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'codigo'             => 'required|string|max:50',
            'facturas'           => 'required|array|min:1',
            'facturas.*.factura' => 'required|string|max:100',
            'facturas.*.monto'   => 'required|numeric|min:0.01',
            'archivos'           => 'nullable|array',
            'archivos.*'         => 'file|max:' . self::MAX_SIZE_KB . '|mimes:' . self::MIMES,
        ], [
            'codigo.required'          => 'El número de voucher es obligatorio.',
            'facturas.required'        => 'Debes agregar al menos una factura.',
            'archivos.*.mimes'         => 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP) y archivos Excel/CSV/PDF.',
            'archivos.*.max'           => 'Cada archivo no puede superar 20 MB.',
        ]);

        $archivosGuardados = [];
        if ($request->hasFile('archivos')) {
            $archivosGuardados = $this->guardarArchivos($request->file('archivos'), $user->sede ?? 'GENERAL');
        }

        $total = collect($request->facturas)->sum('monto');

        $voucher = Voucher::create([
            'codigo'        => strtoupper(trim($request->codigo)),
            'sede'          => $user->sede ?? 'SIN SEDE',
            'status'        => 'pendiente',
            'archivos'      => $archivosGuardados ?: null,
            'total'         => $total,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $user->id,
        ]);

        foreach ($request->facturas as $f) {
            $voucher->facturas()->create([
                'factura' => trim($f['factura']),
                'monto'   => $f['monto'],
            ]);
        }

        $voucher->load(['creator', 'aplicador', 'facturas']);

        // TEMPORAL: notificación desactivada mientras el personal se adapta al sistema
        // $silvia = User::find(self::SILVIA_ID);
        // if ($silvia) {
        //     $silvia->notify(new VoucherEnviadoParaAplicar($voucher));
        // }

        return response()->json([
            'success' => true,
            'message' => 'Voucher creado y enviado a Silvia para aplicar.',
            'voucher' => $this->formatVoucher($voucher),
        ]);
    }

    public function addFactura(Request $request, $id)
    {
        $request->validate([
            'factura' => 'required|string|max:100',
            'monto'   => 'required|numeric|min:0.01',
        ]);

        $voucher = Voucher::findOrFail($id);

        if ($voucher->status === 'aplicado') {
            return response()->json(['success' => false, 'message' => 'No se puede modificar un voucher ya aplicado.'], 422);
        }

        $factura = $voucher->facturas()->create([
            'factura' => trim($request->factura),
            'monto'   => $request->monto,
        ]);

        $nuevoTotal = $voucher->facturas()->sum('monto');
        $voucher->update(['total' => $nuevoTotal]);

        return response()->json([
            'success'     => true,
            'factura'     => ['id' => $factura->id, 'factura' => $factura->factura, 'monto' => $factura->monto],
            'nuevo_total' => $nuevoTotal,
        ]);
    }

    public function removeFactura($id)
    {
        $factura    = VoucherFactura::findOrFail($id);
        $voucherId  = $factura->voucher_id;
        $factura->delete();

        $voucher    = Voucher::findOrFail($voucherId);
        $nuevoTotal = $voucher->facturas()->sum('monto');
        $voucher->update(['total' => $nuevoTotal]);

        return response()->json([
            'success'     => true,
            'nuevo_total' => $nuevoTotal,
        ]);
    }

    public function enviarAplicar($id)
    {
        $voucher = Voucher::with(['facturas', 'creator'])->findOrFail($id);

        if ($voucher->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        // TEMPORAL: notificación desactivada mientras el personal se adapta al sistema
        // $silvia = User::find(self::SILVIA_ID);
        // if ($silvia) {
        //     $silvia->notify(new VoucherEnviadoParaAplicar($voucher));
        // }

        return response()->json([
            'success' => true,
            'message' => 'Notificación reenviada a Silvia.',
        ]);
    }

    public function aplicar($id)
    {
        if (auth()->id() !== self::SILVIA_ID) {
            return response()->json(['success' => false, 'message' => 'Solo Silvia puede aplicar vouchers.'], 403);
        }

        $voucher = Voucher::with(['creator', 'facturas'])->findOrFail($id);

        $voucher->update([
            'status'      => 'aplicado',
            'applied_by'  => auth()->id(),
            'aplicado_at' => now()->toDateString(),
        ]);

        if ($voucher->creator) {
            $voucher->creator->notify(new VoucherAplicado($voucher->fresh()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher aplicado. Se notificó al solicitante.',
            'voucher' => $this->formatVoucher($voucher->fresh()->load(['creator', 'aplicador', 'facturas'])),
        ]);
    }

    public function destroy($id)
    {
        $user    = auth()->user();
        $voucher = Voucher::findOrFail($id);

        if ($voucher->created_by !== $user->id && !$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        foreach ($voucher->archivos ?? [] as $archivo) {
            if (!empty($archivo['path'])) {
                Storage::disk('public')->delete($archivo['path']);
            }
        }

        $voucher->delete();

        return response()->json(['success' => true, 'message' => 'Voucher eliminado.']);
    }

    public function getFacturas($id)
    {
        $voucher = Voucher::with('facturas')->findOrFail($id);

        return response()->json([
            'facturas' => $voucher->facturas->map(fn($f) => [
                'id'      => $f->id,
                'factura' => $f->factura,
                'monto'   => $f->monto,
            ]),
            'archivos' => collect($voucher->archivos ?? [])->map(fn($a) => [
                'name' => $a['name'] ?? 'archivo',
                'url'  => isset($a['path']) ? Storage::disk('public')->url($a['path']) : null,
                'mime' => $a['mime'] ?? '',
                'size' => isset($a['size']) ? round($a['size'] / 1024, 1) . ' KB' : '',
            ]),
        ]);
    }

    /* ── privados ──────────────────────────────────────────── */

    private function guardarArchivos(array $archivos, string $sede): array
    {
        $guardados = [];
        $dir = 'vouchers/' . date('Y/m') . '/' . Str::slug($sede);

        foreach ($archivos as $file) {
            $ext    = $file->getClientOriginalExtension();
            $nombre = $file->getClientOriginalName();
            $path   = $file->storeAs($dir, Str::uuid() . '.' . $ext, 'public');

            $guardados[] = [
                'name' => $nombre,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $guardados;
    }

    private function formatVoucher(Voucher $v): array
    {
        $kpi = null;
        if ($v->solicitado_at) {
            $fin = $v->aplicado_at ?? now()->toDateString();
            $kpi = $v->solicitado_at->diffInDays($fin);
        }

        return [
            'id'             => $v->id,
            'codigo'         => $v->codigo,
            'sede'           => $v->sede,
            'status'         => $v->status,
            'total'          => number_format((float) $v->total, 2),
            'solicitado_at'  => $v->solicitado_at?->format('d/m/Y'),
            'aplicado_at'    => $v->aplicado_at?->format('d/m/Y'),
            'creator_name'   => $v->creator?->name,
            'aplicador_name' => $v->aplicador?->name,
            'kpi'            => $kpi,
            'archivos_count' => count($v->archivos ?? []),
            'facturas'       => $v->facturas->map(fn($f) => [
                'id'      => $f->id,
                'factura' => $f->factura,
                'monto'   => number_format((float) $f->monto, 2),
            ])->toArray(),
        ];
    }
}

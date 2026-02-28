<?php

namespace App\Http\Controllers;

use App\Models\RequerimientoPersonal;
use App\Models\RequerimientoHistorial;
use App\Models\User;
use App\Notifications\RequerimientoCreado;
use App\Notifications\RequerimientoEstadoActualizado;
use App\Notifications\RequerimientoRhAsignado;
use App\Exports\RequerimientosExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class RequerimientoPersonalController extends Controller
{
    const PUESTOS = [
        'Gerente Comercial',
        'Consultor de Marca HOYA',
        'Coordinador de Inteligencia y Planeamiento Com.',
        'Coordinador de Marketing',
        'Consultor Senior',
        'Supervisor de Consultores',
        'Supervisor de ventas',
        'Consultor de monturas 1',
        'Backup Comercial',
        'Consultor de Marca',
        'Responsable de Sede',
        'Consultor de monturas 2',
        'Asistente de Marketing',
        'Asistente de Planeamiento Comercial',
        'Motorizado Back Up',
        'Asistente de Sede',
        'Motorizado',
        'Biselador',
        'Delivery',
        'Asistente de Óptica',
        'Responsable de Óptica',
        'Auxiliar Comercial',
        'Otros',
    ];

    const JEFES_DIRECTOS = [
        'Gerente Comercial',
        'Coordinador de Inteligencia y Planeamiento Com.',
        'Coordinador de Marketing',
        'Supervisor de Consultores',
        'Supervisor de ventas',
    ];

    const EMAILS_NOTIFICACION = [
        'sergio@trimax.pe',
        'christian@trimax.pe',
        'luz@trimax.pe',
        'estefani@trimax.pe',
    ];

    // ─── INDEX ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = RequerimientoPersonal::with(['solicitante', 'responsableRh']);

        if (!$user->puedeVerTodosLosRequerimientos()) {
            abort_unless($user->puedeCrearRequerimientos(), 403);
            $query->where('solicitante_id', $user->id);
        }

        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('tipo'))   $query->where('tipo', $request->tipo);
        if ($request->filled('sede'))   $query->where('sede', $request->sede);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('codigo', 'like', "%$s%")->orWhere('puesto', 'like', "%$s%"));
        }

        $requerimientos = $query->orderBy('fecha_solicitud', 'desc')->paginate(20);
        $sedes = RequerimientoPersonal::select('sede')->distinct()->pluck('sede');

        return view('rrhh.requerimientos.index', compact('requerimientos', 'sedes'));
    }

    // ─── CREATE ────────────────────────────────────────────────

    public function create()
    {
        abort_unless(Auth::user()->puedeCrearRequerimientos(), 403);
        return view('rrhh.requerimientos.create', [
            'puestos'       => self::PUESTOS,
            'jefesDirectos' => self::JEFES_DIRECTOS,
            'sedes'         => $this->getSedes(),
        ]);
    }

    // ─── STORE ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        abort_unless(Auth::user()->puedeCrearRequerimientos(), 403);

        $request->validate([
            'puesto'             => 'required|string',
            'sede'               => 'required|string',
            'jefe_directo'       => 'required|string',
            'tipo'               => 'required|in:Regular,Urgente',
            'condiciones_oferta' => 'nullable|string|max:1000',
            'comentarios'        => 'nullable|string|max:1000',
        ]);

        $requerimiento = DB::transaction(function () use ($request) {
            return RequerimientoPersonal::create([
                'codigo'             => RequerimientoPersonal::generarCodigo(),
                'solicitante_id'     => Auth::id(),
                'gerencia'           => 'GERENCIA COMERCIAL',
                'puesto'             => $request->puesto,
                'sede'               => $request->sede,
                'jefe_directo'       => $request->jefe_directo,
                'tipo'               => $request->tipo,
                'condiciones_oferta' => $request->condiciones_oferta,
                'comentarios'        => $request->comentarios,
                'estado'             => RequerimientoPersonal::ESTADO_PENDIENTE, // ← Inicia en Pendiente
                'fecha_solicitud'    => now(),
            ]);
        });

        $this->registrarHistorial(
            $requerimiento,
            'creacion',
            'Requerimiento creado',
            'Registrado por ' . Auth::user()->name . '. En espera de asignación de responsable RH.'
        );

        $this->notificarDestinatarios($requerimiento, new RequerimientoCreado($requerimiento));

        return redirect()->route('rrhh.requerimientos.index')
            ->with('success', "Requerimiento {$requerimiento->codigo} creado. Estado: Pendiente de asignación.");
    }

    // ─── SHOW ──────────────────────────────────────────────────

    public function show(RequerimientoPersonal $requerimiento)
    {
        $user = Auth::user();
        if (!$user->puedeVerTodosLosRequerimientos()) {
            abort_unless($user->puedeCrearRequerimientos() && $requerimiento->solicitante_id === $user->id, 403);
        }

        $requerimiento->load(['solicitante', 'responsableRh', 'historial.usuario']);
        $usuariosRrhh = User::role('rrhh')->get();
        $etapas       = RequerimientoPersonal::ETAPAS;

        return view('rrhh.requerimientos.show', compact('requerimiento', 'usuariosRrhh', 'etapas'));
    }

    // ─── ACTUALIZAR ESTADO ─────────────────────────────────────

    public function actualizarEstado(Request $request, RequerimientoPersonal $requerimiento)
    {
        abort_unless(Auth::user()->puedeGestionarRequerimientos(), 403);

        $request->validate([
            'estado' => 'required|in:En Proceso,Contratado,Cancelado',
        ]);

        // No permitir volver a Pendiente manualmente
        $estadoAnterior = $requerimiento->estado;
        $estadoNuevo    = $request->estado;

        $requerimiento->update([
            'estado'       => $estadoNuevo,
            'fecha_cierre' => in_array($estadoNuevo, [
                RequerimientoPersonal::ESTADO_CONTRATADO,
                RequerimientoPersonal::ESTADO_CANCELADO
            ]) ? now() : null,
        ]);

        $this->registrarHistorial(
            $requerimiento,
            'cambio_estado',
            "Estado cambiado a {$estadoNuevo}",
            null,
            $estadoAnterior,
            $estadoNuevo
        );

        $this->notificarDestinatarios(
            $requerimiento,
            new RequerimientoEstadoActualizado($requerimiento, $estadoAnterior, $estadoNuevo, 'cambio_estado')
        );

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    // ─── ASIGNAR RESPONSABLE RH ────────────────────────────────
    // Al asignar responsable → cambia automáticamente a "En Proceso"

    public function asignarResponsable(Request $request, RequerimientoPersonal $requerimiento)
    {
        abort_unless(Auth::user()->puedeGestionarRequerimientos(), 403);

        $request->validate([
            'responsable_rh_id'      => 'nullable|exists:users,id',
            'responsable_rh_externo' => 'nullable|string|max:255',
        ]);

        $estadoAnterior = $requerimiento->estado;

        $requerimiento->update([
            'responsable_rh_id'      => $request->responsable_rh_id,
            'responsable_rh_externo' => $request->responsable_rh_externo,
            'estado'                 => RequerimientoPersonal::ESTADO_EN_PROCESO, // ← Cambia automáticamente
        ]);

        $nombre = $request->responsable_rh_id
            ? User::find($request->responsable_rh_id)->name
            : $request->responsable_rh_externo;

        // Registrar asignación en historial
        $this->registrarHistorial(
            $requerimiento,
            'asignacion_rh',
            "Responsable RH asignado: {$nombre}",
            "Se asignó a {$nombre} como responsable del proceso de selección."
        );

        // Si cambió de Pendiente a En Proceso, registrar ese cambio también
        if ($estadoAnterior === RequerimientoPersonal::ESTADO_PENDIENTE) {
            $this->registrarHistorial(
                $requerimiento,
                'cambio_estado',
                'Requerimiento pasó a En Proceso',
                'El estado cambió automáticamente al asignar un responsable RH.',
                $estadoAnterior,
                RequerimientoPersonal::ESTADO_EN_PROCESO
            );
        }

        // Notificar al responsable interno asignado
        if ($request->responsable_rh_id) {
            User::find($request->responsable_rh_id)->notify(new RequerimientoRhAsignado($requerimiento));
        }

        // Notificar a todos los involucrados
        $this->notificarDestinatarios(
            $requerimiento,
            new RequerimientoEstadoActualizado(
                $requerimiento,
                $estadoAnterior,
                RequerimientoPersonal::ESTADO_EN_PROCESO,
                'asignacion_rh',
                $nombre
            )
        );

        return back()->with('success', "Responsable asignado. El requerimiento ahora está En Proceso.");
    }

    // ─── REGISTRAR ETAPA DEL PROCESO ──────────────────────────
    // El responsable RH registra avances del proceso de selección

    public function registrarEtapa(Request $request, RequerimientoPersonal $requerimiento)
    {
        abort_unless(Auth::user()->puedeGestionarRequerimientos(), 403);

        // Solo se puede registrar etapas si está En Proceso
        abort_unless($requerimiento->estado === RequerimientoPersonal::ESTADO_EN_PROCESO, 422);

        $etapasValidas = array_keys(RequerimientoPersonal::ETAPAS);

        $request->validate([
            'tipo_etapa'  => 'required|in:' . implode(',', $etapasValidas),
            'descripcion' => 'required|string|max:2000',
        ]);

        $tipoEtapa = $request->tipo_etapa;
        $etiqueta  = RequerimientoPersonal::ETAPAS[$tipoEtapa];

        // Para nota libre usamos 'nota', para el resto el tipo exacto
        $this->registrarHistorial(
            $requerimiento,
            $tipoEtapa,
            $etiqueta,
            $request->descripcion
        );

        // Notificar al solicitante sobre el avance
        $requerimiento->solicitante->notify(
            new RequerimientoEstadoActualizado(
                $requerimiento,
                null,
                null,
                'etapa',
                "[{$etiqueta}] " . $request->descripcion
            )
        );

        return back()->with('success', "Etapa \"{$etiqueta}\" registrada correctamente.");
    }

    // ─── EXPORT EXCEL ─────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        abort_unless(Auth::user()->puedeVerTodosLosRequerimientos(), 403);
        return Excel::download(new RequerimientosExport($request), 'requerimientos_personal.xlsx');
    }

    // ─── DASHBOARD ────────────────────────────────────────────

    public function dashboard()
    {
        abort_unless(Auth::user()->puedeVerTodosLosRequerimientos(), 403);

        $total      = RequerimientoPersonal::count();
        $pendientes = RequerimientoPersonal::pendientes()->count();
        $enProceso  = RequerimientoPersonal::enProceso()->count();
        $contratados = RequerimientoPersonal::where('estado', RequerimientoPersonal::ESTADO_CONTRATADO)->count();
        $cancelados = RequerimientoPersonal::where('estado', RequerimientoPersonal::ESTADO_CANCELADO)->count();

        $contratadosDentroSla = RequerimientoPersonal::where('estado', RequerimientoPersonal::ESTADO_CONTRATADO)
            ->whereRaw('DATEDIFF(fecha_cierre, fecha_solicitud) <= 45')->count();

        $porcentajeCumplimiento = $total > 0
            ? round(($contratadosDentroSla / $total) * 100, 2) : 0;

        $semaforoColor = $porcentajeCumplimiento >= 80 ? 'green' : 'red';

        $requerimientosGrafico = RequerimientoPersonal::select('codigo', 'fecha_solicitud', 'fecha_cierre', 'estado')
            ->orderBy('fecha_solicitud', 'desc')->limit(30)->get()
            ->map(fn($r) => [
                'codigo'   => $r->codigo,
                'kpi'      => $r->kpi,
                'sla'      => 45,
                'semaforo' => $r->semaforo,
                'color'    => $r->color_semaforo,
            ]);

        $optimo  = RequerimientoPersonal::where('estado', RequerimientoPersonal::ESTADO_CONTRATADO)->whereRaw('DATEDIFF(fecha_cierre, fecha_solicitud) <= 45')->count();
        $riesgo  = RequerimientoPersonal::where('estado', RequerimientoPersonal::ESTADO_CONTRATADO)->whereRaw('DATEDIFF(fecha_cierre, fecha_solicitud) BETWEEN 46 AND 60')->count();
        $critico = RequerimientoPersonal::where('estado', RequerimientoPersonal::ESTADO_CONTRATADO)->whereRaw('DATEDIFF(fecha_cierre, fecha_solicitud) > 60')->count();

        return view('rrhh.requerimientos.dashboard', compact(
            'total',
            'pendientes',
            'enProceso',
            'contratados',
            'cancelados',
            'porcentajeCumplimiento',
            'semaforoColor',
            'requerimientosGrafico',
            'optimo',
            'riesgo',
            'critico'
        ));
    }

    // ─── HELPERS ──────────────────────────────────────────────

    private function registrarHistorial(
        RequerimientoPersonal $req,
        string $tipoEvento,
        string $titulo,
        ?string $descripcion   = null,
        ?string $estadoAnterior = null,
        ?string $estadoNuevo   = null
    ): void {
        RequerimientoHistorial::create([
            'requerimiento_id' => $req->id,
            'user_id'          => Auth::id(),
            'tipo_evento'      => $tipoEvento,
            'titulo'           => $titulo,
            'descripcion'      => $descripcion,
            'estado_anterior'  => $estadoAnterior,
            'estado_nuevo'     => $estadoNuevo,
        ]);
    }

    private function notificarDestinatarios(RequerimientoPersonal $req, $notification): void
    {
        $destinatarios = User::where(function ($q) use ($req) {
            $q->whereIn('email', self::EMAILS_NOTIFICACION)
                ->orWhere('id', $req->solicitante_id);
        })->get();
        Notification::send($destinatarios, $notification);
    }

    private function getSedes(): array
    {
        return [
            'NAPO',
            'Lince',
            'Cusco',
            'Miraflores',
            'San Isidro',
            'Surco',
            'La Molina',
            'San Borja',
            'Pueblo Libre',
            'Jesús María',
            'Magdalena',
            'Barranco',
            'Chorrillos',
            'Surquillo',
            'Ate',
            'Santa Anita',
            'San Juan de Lurigancho',
            'Los Olivos',
            'Independencia',
            'Callao',
            'Bellavista',
            'Arequipa',
            'Trujillo',
            'Piura',
            'Chiclayo',
            'COMERCIAL GENERAL',
        ];
    }
}

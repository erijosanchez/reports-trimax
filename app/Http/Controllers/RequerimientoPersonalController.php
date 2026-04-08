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
use Barryvdh\DomPDF\Facade\Pdf;

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
            'puesto'              => 'required|string',
            'sede'                => 'required|string',
            'jefe_directo'        => 'required|string',
            'supervisa_a'         => 'nullable|string|max:255',
            'num_vacantes'        => 'nullable|integer|min:1|max:99',
            'info_confidencial'   => 'nullable|boolean',
            'tipo'                => 'required|in:Regular,Urgente',
            'tipo_vacante'        => 'nullable|in:vacante,reemplazo,posicion_nueva',
            'permanencia'         => 'nullable|in:temporal,permanente',
            'disponibilidad_viaje'=> 'nullable|boolean',
            'jornada'             => 'nullable|in:tiempo_parcial,tiempo_completo',
            'condiciones_oferta'  => 'nullable|string|max:1000',
            'comentarios'         => 'nullable|string|max:1000',
            'motivo'              => 'nullable|string|max:500',
            'candidatos'          => 'nullable|array|max:3',
            'candidatos.*.nombre' => 'nullable|string|max:255',
            'candidatos.*.telefono' => 'nullable|string|max:30',
            'herramientas'        => 'nullable|array|max:3',
            'herramientas.*'      => 'nullable|string|max:255',
        ]);

        // Limpiar candidatos/herramientas vacíos
        $candidatos = collect($request->candidatos ?? [])
            ->filter(fn($c) => !empty($c['nombre']))
            ->values()->toArray();

        $herramientas = collect($request->herramientas ?? [])
            ->filter(fn($h) => !empty($h))
            ->values()->toArray();

        $requerimiento = DB::transaction(function () use ($request, $candidatos, $herramientas) {
            return RequerimientoPersonal::create([
                'codigo'               => RequerimientoPersonal::generarCodigo(),
                'solicitante_id'       => Auth::id(),
                'gerencia'             => 'GERENCIA COMERCIAL',
                'puesto'               => $request->puesto,
                'sede'                 => $request->sede,
                'jefe_directo'         => $request->jefe_directo,
                'supervisa_a'          => $request->supervisa_a,
                'num_vacantes'         => $request->num_vacantes ?? 1,
                'info_confidencial'    => $request->boolean('info_confidencial'),
                'tipo'                 => $request->tipo,
                'tipo_vacante'         => $request->tipo_vacante,
                'permanencia'          => $request->permanencia,
                'disponibilidad_viaje' => $request->boolean('disponibilidad_viaje'),
                'jornada'              => $request->jornada,
                'condiciones_oferta'   => $request->condiciones_oferta,
                'comentarios'          => $request->comentarios,
                'motivo'               => $request->motivo,
                'candidatos'           => !empty($candidatos) ? $candidatos : null,
                'herramientas'         => !empty($herramientas) ? $herramientas : null,
                'estado'               => RequerimientoPersonal::ESTADO_PENDIENTE,
                'fecha_solicitud'      => now(),
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
        $usuariosRrhh  = User::role('rrhh')->get();
        $etapas        = RequerimientoPersonal::ETAPAS;
        $gerenteGeneral = User::where('es_gerente_general', true)->first();

        return view('rrhh.requerimientos.show', compact(
            'requerimiento', 'usuariosRrhh', 'etapas', 'gerenteGeneral'
        ));
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

    // ─── ACTUALIZAR CANDIDATOS Y HERRAMIENTAS (Secciones 4 y 5) ──

    public function actualizarCandidatosHerramientas(Request $request, RequerimientoPersonal $requerimiento)
    {
        $user = Auth::user();

        // Puede editar: el solicitante o quien gestiona RRHH
        $puedeEditar = $requerimiento->solicitante_id === $user->id
            || $user->puedeGestionarRequerimientos();

        abort_unless($puedeEditar, 403);

        $request->validate([
            'candidatos'            => 'nullable|array|max:3',
            'candidatos.*.nombre'   => 'nullable|string|max:255',
            'candidatos.*.telefono' => 'nullable|string|max:30',
            'herramientas'          => 'nullable|array|max:3',
            'herramientas.*'        => 'nullable|string|max:255',
        ]);

        $candidatos = collect($request->candidatos ?? [])
            ->filter(fn($c) => !empty($c['nombre']))
            ->values()->toArray();

        $herramientas = collect($request->herramientas ?? [])
            ->filter(fn($h) => !empty($h))
            ->values()->toArray();

        $requerimiento->update([
            'candidatos'  => !empty($candidatos) ? $candidatos : null,
            'herramientas'=> !empty($herramientas) ? $herramientas : null,
        ]);

        return back()->with('success', 'Candidatos y herramientas actualizados.');
    }

    // ─── ACTUALIZAR INFO RRHH (Sección 3) ─────────────────────

    public function actualizarInfoRrhh(Request $request, RequerimientoPersonal $requerimiento)
    {
        abort_unless(Auth::user()->puedeGestionarRequerimientos(), 403);

        $request->validate([
            'fecha_estimada_contratacion' => 'nullable|date',
            'tipo_contrato'               => 'nullable|string|max:255',
            'duracion_contrato'           => 'nullable|string|max:255',
            'remuneracion_prevista'       => 'nullable|numeric|min:0',
            'horario_trabajo'             => 'nullable|string|max:255',
            'beneficios'                  => 'nullable|string|max:1000',
        ]);

        $requerimiento->update($request->only([
            'fecha_estimada_contratacion',
            'tipo_contrato',
            'duracion_contrato',
            'remuneracion_prevista',
            'horario_trabajo',
            'beneficios',
        ]));

        $this->registrarHistorial(
            $requerimiento,
            'nota',
            'Información RRHH actualizada',
            'Se actualizó la sección de información de RRHH del formulario.'
        );

        return back()->with('success', 'Información de RRHH guardada correctamente.');
    }

    // ─── FIRMAR ────────────────────────────────────────────────

    public function firmar(Request $request, RequerimientoPersonal $requerimiento)
    {
        $user = Auth::user();

        // Verificar que el usuario tiene firma registrada
        abort_unless($user->tieneFirmaRegistrada(), 422);

        // Determinar qué rol firma
        $rol = $this->determinarRolFirma($user, $requerimiento);
        abort_unless($rol !== null, 403);

        // No firmar dos veces
        $campoData = "firma_{$rol}_data";
        if (!empty($requerimiento->$campoData)) {
            return back()->with('error', 'Este espacio ya fue firmado.');
        }

        $requerimiento->update([
            "firma_{$rol}_data"   => $user->firma_imagen,
            "firma_{$rol}_at"     => now(),
            "firma_{$rol}_nombre" => $user->name . ($user->cargo ? ' - ' . $user->cargo : ''),
        ]);

        $etiquetas = [
            'solicitante' => 'Responsable de Área Solicitante',
            'rrhh'        => 'Responsable de Recursos Humanos',
            'gerente'     => 'Gerente General',
        ];

        $this->registrarHistorial(
            $requerimiento,
            'nota',
            "Firmado por {$etiquetas[$rol]}",
            $user->name . ' firmó el formulario como ' . $etiquetas[$rol] . '.'
        );

        return back()->with('success', 'Firmado correctamente.');
    }

    private function determinarRolFirma($user, RequerimientoPersonal $requerimiento): ?string
    {
        // Gerente General
        if ($user->esGerenteGeneral()) return 'gerente';

        // Responsable RRHH asignado al requerimiento
        if ($user->isRrhh() || ($requerimiento->responsable_rh_id === $user->id)) return 'rrhh';

        // Solicitante (responsable de área)
        if ($requerimiento->solicitante_id === $user->id) return 'solicitante';

        // Superadmin puede firmar como gerente si nadie más lo es
        if ($user->isSuperAdmin()) return 'gerente';

        return null;
    }

    // ─── GENERAR PDF ───────────────────────────────────────────

    public function generarPdf(RequerimientoPersonal $requerimiento)
    {
        $user = Auth::user();

        // Solo quien puede ver el requerimiento puede descargar el PDF
        if (!$user->puedeVerTodosLosRequerimientos()) {
            abort_unless(
                $user->puedeCrearRequerimientos() && $requerimiento->solicitante_id === $user->id,
                403
            );
        }

        $requerimiento->load(['solicitante', 'responsableRh']);

        $logoPath = public_path('assets/img/ltr.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('rrhh.requerimientos.pdf', compact('requerimiento', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'dpi'                  => 96,
                'isFontSubsettingEnabled' => true,
            ]);

        $nombre = 'RH-PR-03-FO-01_' . $requerimiento->codigo . '.pdf';

        return $pdf->download($nombre);
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

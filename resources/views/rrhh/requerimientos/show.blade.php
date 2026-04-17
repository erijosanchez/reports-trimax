{{-- resources/views/rrhh/requerimientos/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalle - ' . $requerimiento->codigo)

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">

                {{-- HEADER --}}
                <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom">
                    <div>
                        <a href="{{ route('rrhh.requerimientos.index') }}"
                            class="d-block mb-1 text-muted text-decoration-none">
                            <i class="mdi-arrow-left mdi"></i> Volver al listado
                        </a>
                        <h3 class="mb-0">
                            <i class="mdi mdi-account-search"></i>
                            Requerimiento
                            <span class="font-monospace text-primary">{{ $requerimiento->codigo }}</span>
                        </h3>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                        @php
                            $badgeClass = match ($requerimiento->estado) {
                                'Pendiente' => 'bg-info',
                                'En Proceso' => 'bg-warning text-dark',
                                'Contratado' => 'bg-success',
                                'Cancelado' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} fs-6 px-3 py-2">
                            {{ $requerimiento->estado }}
                        </span>
                        @if ($requerimiento->tipo === 'Urgente')
                            <span class="bg-danger px-3 py-2 badge fs-6">
                                <i class="mdi mdi-lightning-bolt"></i> Urgente
                            </span>
                        @else
                            <span class="bg-primary px-3 py-2 badge fs-6">Regular</span>
                        @endif
                        <a href="{{ route('rrhh.requerimientos.pdf', $requerimiento->id) }}?inline=1"
                            target="_blank" class="btn btn-sm btn-outline-secondary ms-1">
                            <i class="mdi mdi-eye-outline"></i> Vista previa
                        </a>
                        <a href="{{ route('rrhh.requerimientos.pdf', $requerimiento->id) }}"
                            class="btn btn-sm btn-outline-danger ms-1">
                            <i class="mdi mdi-file-pdf-box"></i> Descargar PDF
                        </a>
                    </div>
                </div>

                {{-- ALERTAS --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($requerimiento->estado === 'Pendiente')
                    <div class="d-flex align-items-center gap-2 alert alert-info">
                        <i class="mdi mdi-information mdi-24px"></i>
                        <span>Este requerimiento está <strong>pendiente de asignación</strong>. El proceso iniciará
                            automáticamente al asignar un responsable RH.</span>
                    </div>
                @endif

                <div class="row">

                    @php
                        $tieneColumnaLateral = auth()->user()->puedeGestionarRequerimientos()
                            || auth()->user()->esGerenteGeneral()
                            || $requerimiento->solicitante_id === auth()->id();
                    @endphp
                    {{-- COLUMNA PRINCIPAL --}}
                    <div class="{{ $tieneColumnaLateral ? 'col-lg-8' : 'col-lg-12' }}">

                        {{-- DATOS DEL REQUERIMIENTO --}}
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-4 text-primary">
                                    <i class="mdi-briefcase-outline mdi"></i> Datos del Requerimiento
                                </h6>
                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">GERENCIA</p>
                                        <p class="mb-0">{{ $requerimiento->gerencia }}</p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">PUESTO</p>
                                        <p class="mb-0 fw-bold">{{ $requerimiento->puesto }}</p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">SEDE</p>
                                        <p class="mb-0">{{ $requerimiento->sede }}</p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">JEFE DIRECTO</p>
                                        <p class="mb-0">{{ $requerimiento->jefe_directo }}</p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">SOLICITANTE</p>
                                        <p class="mb-0">{{ $requerimiento->solicitante->name }}</p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">RESPONSABLE RH</p>
                                        <p class="mb-0">
                                            @if ($requerimiento->responsableRh)
                                                {{ $requerimiento->responsableRh->name }}
                                            @elseif($requerimiento->responsable_rh_externo)
                                                {{ $requerimiento->responsable_rh_externo }}
                                            @else
                                                <span class="text-muted">Sin asignar</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">FECHA SOLICITUD</p>
                                        <p class="mb-0">{{ $requerimiento->fecha_solicitud->format('d/m/Y H:i') }}</p>
                                    </div>
                                    @if ($requerimiento->fecha_cierre)
                                        <div class="mb-3 col-md-4">
                                            <p class="mb-1 text-muted small fw-semibold">FECHA CIERRE</p>
                                            <p class="mb-0">{{ $requerimiento->fecha_cierre->format('d/m/Y H:i') }}</p>
                                        </div>
                                    @endif
                                    @if ($requerimiento->condiciones_oferta)
                                        <div class="mb-3 col-md-12">
                                            <p class="mb-1 text-muted small fw-semibold">CONDICIONES DE LA OFERTA</p>
                                            <p class="mb-0">{{ $requerimiento->condiciones_oferta }}</p>
                                        </div>
                                    @endif
                                    @if ($requerimiento->comentarios)
                                        <div class="mb-3 col-md-12">
                                            <p class="mb-1 text-muted small fw-semibold">COMENTARIOS DEL SOLICITANTE</p>
                                            <p class="mb-0">{{ $requerimiento->comentarios }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- KPI METRICS (solo si no está Pendiente) --}}
                        @if ($requerimiento->estado !== 'Pendiente')
                            <div class="mb-4 row">
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">SLA</p>
                                            <h2 class="mb-0 text-dark">{{ $requerimiento->sla }}</h2>
                                            <small class="text-muted">días máx.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">KPI</p>
                                            @php
                                                $kpiColor = match ($requerimiento->semaforo) {
                                                    'optimo' => 'text-success',
                                                    'riesgo' => 'text-warning',
                                                    'critico' => 'text-danger',
                                                    default => 'text-muted',
                                                };
                                            @endphp
                                            <h2 class="mb-0 {{ $kpiColor }}">{{ $requerimiento->kpi }}%</h2>
                                            <small class="text-muted">cumplimiento</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">TOTAL DÍAS</p>
                                            <h2 class="mb-0 text-dark">{{ $requerimiento->total }}</h2>
                                            <small class="text-muted">días del proceso</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">SEMÁFORO</p>
                                            @php
                                                $semaforoIcon = match ($requerimiento->semaforo) {
                                                    'optimo' => 'mdi-circle text-success',
                                                    'riesgo' => 'mdi-circle text-warning',
                                                    'critico' => 'mdi-circle text-danger',
                                                    default => 'mdi-circle text-muted',
                                                };
                                                $semaforoLabel = match ($requerimiento->semaforo) {
                                                    'optimo' => 'Óptimo',
                                                    'riesgo' => 'En Riesgo',
                                                    'critico' => 'Crítico',
                                                    default => '—',
                                                };
                                            @endphp
                                            <i class="mdi {{ $semaforoIcon }} mdi-36px d-block"></i>
                                            <small class="text-muted">{{ $semaforoLabel }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- DETALLE FORMULARIO (campos adicionales sección 1 y 2) --}}
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-3 text-primary">
                                    <i class="mdi mdi-clipboard-text-outline"></i> Detalle del Formulario
                                </h6>
                                <div class="row">
                                    @if($requerimiento->supervisa_a)
                                    <div class="mb-2 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">SUPERVISA A</p>
                                        <p class="mb-0">{{ $requerimiento->supervisa_a }}</p>
                                    </div>
                                    @endif
                                    <div class="mb-2 col-md-2">
                                        <p class="mb-1 text-muted small fw-semibold">VACANTES</p>
                                        <p class="mb-0">{{ $requerimiento->num_vacantes ?? 1 }}</p>
                                    </div>
                                    @if($requerimiento->tipo_vacante)
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">TIPO VACANTE</p>
                                        <p class="mb-0">{{ ['vacante'=>'Vacante','reemplazo'=>'Reemplazo','posicion_nueva'=>'Posición nueva'][$requerimiento->tipo_vacante] }}</p>
                                    </div>
                                    @endif
                                    @if($requerimiento->permanencia)
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">PERMANENCIA</p>
                                        <p class="mb-0">{{ ['temporal'=>'Temporal','permanente'=>'Permanente'][$requerimiento->permanencia] }}</p>
                                    </div>
                                    @endif
                                    @if($requerimiento->jornada)
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">JORNADA</p>
                                        <p class="mb-0">{{ ['tiempo_parcial'=>'Tiempo Parcial','tiempo_completo'=>'Tiempo Completo'][$requerimiento->jornada] }}</p>
                                    </div>
                                    @endif
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">DISPONIB. VIAJE</p>
                                        <p class="mb-0">{{ $requerimiento->disponibilidad_viaje ? 'Sí' : 'No' }}</p>
                                    </div>
                                    @if($requerimiento->motivo)
                                    <div class="mb-2 col-md-12">
                                        <p class="mb-1 text-muted small fw-semibold">MOTIVO</p>
                                        <p class="mb-0">{{ $requerimiento->motivo }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- SECCIONES 4 Y 5: Candidatos y Herramientas (editable por solicitante y RRHH) --}}
                        @php
                            $puedeEditarSec45 = $requerimiento->solicitante_id === auth()->id()
                                || auth()->user()->puedeGestionarRequerimientos();
                        @endphp
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-1 text-primary">
                                    <i class="mdi mdi-account-multiple-outline"></i> Sección 4 y 5 — Candidatos y Herramientas
                                </h6>
                                <p class="text-muted small mb-3">Puede ser completado por el solicitante o por RRHH.</p>

                                @if($puedeEditarSec45)
                                <form action="{{ route('rrhh.requerimientos.candidatos', $requerimiento->id) }}" method="POST">
                                    @csrf @method('PATCH')

                                    <p class="fw-semibold small mb-2">4. Candidatos a considerar (máx. 3)</p>
                                    @foreach(['a','b','c'] as $i => $letra)
                                        @php
                                            $cand = $requerimiento->candidatos[$i] ?? null;
                                        @endphp
                                        <div class="row mb-2">
                                            <div class="col-md-7">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">{{ strtoupper($letra) }}.</span>
                                                    <input type="text" name="candidatos[{{ $i }}][nombre]"
                                                        class="form-control"
                                                        value="{{ $cand['nombre'] ?? '' }}"
                                                        placeholder="Nombre completo">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="candidatos[{{ $i }}][telefono]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $cand['telefono'] ?? '' }}"
                                                    placeholder="Teléfono">
                                            </div>
                                        </div>
                                    @endforeach

                                    <hr>
                                    <p class="fw-semibold small mb-2">5. Herramientas que el puesto requiere (máx. 3)</p>
                                    @foreach(['a','b','c'] as $i => $letra)
                                        <div class="mb-2">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">{{ strtoupper($letra) }}.</span>
                                                <input type="text" name="herramientas[{{ $i }}]"
                                                    class="form-control"
                                                    value="{{ $requerimiento->herramientas[$i] ?? '' }}"
                                                    placeholder="Ej: Excel avanzado, SAP...">
                                            </div>
                                        </div>
                                    @endforeach

                                    <button type="submit" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="mdi mdi-content-save"></i> Guardar candidatos y herramientas
                                    </button>
                                </form>
                                @else
                                    {{-- Vista solo lectura --}}
                                    @if($requerimiento->candidatos)
                                        <p class="fw-semibold small mb-1">4. Candidatos</p>
                                        @foreach($requerimiento->candidatos as $i => $c)
                                            <div class="d-flex gap-3 mb-1 small">
                                                <span class="text-muted">{{ ['a','b','c'][$i] ?? ($i+1) }}.</span>
                                                <span>{{ $c['nombre'] }}</span>
                                                @if(!empty($c['telefono']))<span class="text-muted">— {{ $c['telefono'] }}</span>@endif
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted small">4. Sin candidatos registrados.</p>
                                    @endif
                                    @if($requerimiento->herramientas)
                                        <p class="fw-semibold small mb-1 mt-2">5. Herramientas</p>
                                        @foreach($requerimiento->herramientas as $i => $h)
                                            <div class="small mb-1">
                                                <span class="text-muted">{{ ['a','b','c'][$i] ?? ($i+1) }}.</span> {{ $h }}
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted small mt-2">5. Sin herramientas registradas.</p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- SECCIÓN RRHH (solo visible para quien puede gestionar) --}}
                        @if(auth()->user()->puedeGestionarRequerimientos())
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-3" style="color:#5c5c5c;">
                                    <i class="mdi mdi-lock-outline"></i> Sección RRHH — Información del contrato
                                </h6>
                                @if($requerimiento->fecha_estimada_contratacion || $requerimiento->tipo_contrato)
                                <div class="row mb-3">
                                    @if($requerimiento->fecha_estimada_contratacion)
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">FECHA EST. CONTRATACIÓN</p>
                                        <p class="mb-0">{{ $requerimiento->fecha_estimada_contratacion->format('d/m/Y') }}</p>
                                    </div>
                                    @endif
                                    @if($requerimiento->tipo_contrato)
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">TIPO DE CONTRATO</p>
                                        <p class="mb-0">{{ $requerimiento->tipo_contrato }}</p>
                                    </div>
                                    @endif
                                    @if($requerimiento->duracion_contrato)
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">DURACIÓN</p>
                                        <p class="mb-0">{{ $requerimiento->duracion_contrato }}</p>
                                    </div>
                                    @endif
                                    @if($requerimiento->remuneracion_prevista)
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">REMUNERACIÓN S/.</p>
                                        <p class="mb-0">{{ number_format($requerimiento->remuneracion_prevista, 2) }}</p>
                                    </div>
                                    @endif
                                    @if($requerimiento->horario_trabajo)
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">HORARIO</p>
                                        <p class="mb-0">{{ $requerimiento->horario_trabajo }}</p>
                                    </div>
                                    @endif
                                    @if($requerimiento->beneficios)
                                    <div class="col-md-12 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">BENEFICIOS</p>
                                        <p class="mb-0">{{ $requerimiento->beneficios }}</p>
                                    </div>
                                    @endif
                                </div>
                                <hr>
                                @endif
                                {{-- Formulario para llenar sección RRHH --}}
                                <form action="{{ route('rrhh.requerimientos.info-rrhh', $requerimiento->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <div class="row">
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">FECHA EST. CONTRATACIÓN</label>
                                            <input type="date" name="fecha_estimada_contratacion" class="form-control form-control-sm"
                                                value="{{ $requerimiento->fecha_estimada_contratacion?->format('Y-m-d') }}">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">TIPO DE CONTRATO</label>
                                            <input type="text" name="tipo_contrato" class="form-control form-control-sm"
                                                value="{{ $requerimiento->tipo_contrato }}"
                                                placeholder="Ej: Indeterminado, Plazo fijo...">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">DURACIÓN</label>
                                            <input type="text" name="duracion_contrato" class="form-control form-control-sm"
                                                value="{{ $requerimiento->duracion_contrato }}"
                                                placeholder="Ej: 3 meses, 1 año...">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">REMUNERACIÓN S/.</label>
                                            <input type="number" step="0.01" name="remuneracion_prevista" class="form-control form-control-sm"
                                                value="{{ $requerimiento->remuneracion_prevista }}"
                                                placeholder="0.00">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">HORARIO DE TRABAJO</label>
                                            <input type="text" name="horario_trabajo" class="form-control form-control-sm"
                                                value="{{ $requerimiento->horario_trabajo }}"
                                                placeholder="Ej: Lun-Sáb 9am-6pm">
                                        </div>
                                        <div class="mb-2 col-md-12">
                                            <label class="form-label small fw-semibold">BENEFICIOS</label>
                                            <textarea name="beneficios" class="form-control form-control-sm" rows="2"
                                                placeholder="Ej: Seguro médico, gratificaciones...">{{ $requerimiento->beneficios }}</textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary mt-2">
                                        <i class="mdi mdi-content-save"></i> Guardar información RRHH
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif

                        {{-- FIRMAS --}}
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-3 text-primary">
                                    <i class="mdi mdi-draw"></i> Aprobaciones y Firmas
                                </h6>
                                <div class="row text-center">
                                    @php
                                        $user = auth()->user();
                                        $puedeFirmarSolicitante = $requerimiento->solicitante_id === $user->id && empty($requerimiento->firma_solicitante_data);
                                        $puedeFirmarRrhh        = ($user->isRrhh() || $user->puedeGestionarRequerimientos()) && !$user->esGerenteGeneral() && empty($requerimiento->firma_rrhh_data);
                                        $puedeFirmarGerente     = $user->esGerenteGeneral() && empty($requerimiento->firma_gerente_data);
                                    @endphp

                                    {{-- Solicitante --}}
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3" style="min-height:110px;">
                                            @if($requerimiento->firma_solicitante_data)
                                                <img src="{{ $requerimiento->firma_solicitante_data }}" style="max-height:60px; max-width:100%;" alt="firma">
                                                <div class="small text-success mt-1"><i class="mdi mdi-check-circle"></i> Firmado</div>
                                                <div class="small text-muted">{{ $requerimiento->firma_solicitante_nombre }}</div>
                                                <div class="small text-muted">{{ $requerimiento->firma_solicitante_at?->format('d/m/Y H:i') }}</div>
                                            @elseif($puedeFirmarSolicitante)
                                                @if($user->tieneFirmaRegistrada())
                                                    <form method="POST" action="{{ route('rrhh.requerimientos.firmar', $requerimiento->id) }}"
                                                        onsubmit="return confirm('¿Confirmas tu firma como Responsable de Área Solicitante?')">
                                                        @csrf
                                                        <p class="text-muted small mb-2">Pendiente de tu firma</p>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-draw"></i> Firmar
                                                        </button>
                                                    </form>
                                                @else
                                                    <p class="text-muted small mb-2">Pendiente de firma</p>
                                                    <a href="{{ route('firma.index') }}" class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-alert"></i> Registra tu firma primero
                                                    </a>
                                                @endif
                                            @else
                                                <div class="text-muted" style="padding-top:30px;">
                                                    <i class="mdi mdi-dots-horizontal mdi-24px"></i>
                                                    <div class="small">Pendiente</div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="fw-bold small mt-2">Responsable de Área Solicitante</div>
                                    </div>

                                    {{-- RRHH --}}
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3" style="min-height:110px;">
                                            @if($requerimiento->firma_rrhh_data)
                                                <img src="{{ $requerimiento->firma_rrhh_data }}" style="max-height:60px; max-width:100%;" alt="firma">
                                                <div class="small text-success mt-1"><i class="mdi mdi-check-circle"></i> Firmado</div>
                                                <div class="small text-muted">{{ $requerimiento->firma_rrhh_nombre }}</div>
                                                <div class="small text-muted">{{ $requerimiento->firma_rrhh_at?->format('d/m/Y H:i') }}</div>
                                            @elseif($puedeFirmarRrhh)
                                                @if($user->tieneFirmaRegistrada())
                                                    <form method="POST" action="{{ route('rrhh.requerimientos.firmar', $requerimiento->id) }}"
                                                        onsubmit="return confirm('¿Confirmas tu firma como Responsable de RRHH?')">
                                                        @csrf
                                                        <p class="text-muted small mb-2">Pendiente de tu firma</p>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-draw"></i> Firmar
                                                        </button>
                                                    </form>
                                                @else
                                                    <p class="text-muted small mb-2">Pendiente de firma</p>
                                                    <a href="{{ route('firma.index') }}" class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-alert"></i> Registra tu firma primero
                                                    </a>
                                                @endif
                                            @else
                                                <div class="text-muted" style="padding-top:30px;">
                                                    <i class="mdi mdi-dots-horizontal mdi-24px"></i>
                                                    <div class="small">Pendiente</div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="fw-bold small mt-2">Responsable de Recursos Humanos</div>
                                    </div>

                                    {{-- Gerente General --}}
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3" style="min-height:110px;">
                                            @if($requerimiento->firma_gerente_data)
                                                <img src="{{ $requerimiento->firma_gerente_data }}" style="max-height:60px; max-width:100%;" alt="firma">
                                                <div class="small text-success mt-1"><i class="mdi mdi-check-circle"></i> Firmado</div>
                                                <div class="small text-muted">{{ $requerimiento->firma_gerente_nombre }}</div>
                                                <div class="small text-muted">{{ $requerimiento->firma_gerente_at?->format('d/m/Y H:i') }}</div>
                                            @elseif($puedeFirmarGerente)
                                                @if($user->tieneFirmaRegistrada())
                                                    <form method="POST" action="{{ route('rrhh.requerimientos.firmar', $requerimiento->id) }}"
                                                        onsubmit="return confirm('¿Confirmas tu firma como Gerente General?')">
                                                        @csrf
                                                        <p class="text-muted small mb-2">Pendiente de tu firma</p>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-draw"></i> Firmar
                                                        </button>
                                                    </form>
                                                @else
                                                    <p class="text-muted small mb-2">Pendiente de firma</p>
                                                    <a href="{{ route('firma.index') }}" class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-alert"></i> Registra tu firma primero
                                                    </a>
                                                @endif
                                            @else
                                                <div class="text-muted" style="padding-top:30px;">
                                                    <i class="mdi mdi-dots-horizontal mdi-24px"></i>
                                                    <div class="small">Pendiente</div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="fw-bold small mt-2">Gerente General</div>
                                        @if($gerenteGeneral)
                                            <div class="text-muted small">{{ $gerenteGeneral->name }}</div>
                                        @else
                                            <div class="text-danger small"><i class="mdi mdi-alert-circle-outline"></i> Sin Gerente General configurado</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-muted small mt-1">
                                    <i class="mdi mdi-information-outline"></i>
                                    Para poder firmar, cada usuario debe tener su <a href="{{ route('firma.index') }}">firma digital registrada</a>.
                                </div>
                            </div>
                        </div>

                        {{-- HISTORIAL / TIMELINE --}}
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h6 class="mb-4 text-primary">
                                    <i class="mdi mdi-history"></i> Historial del Proceso
                                </h6>

                                @forelse($requerimiento->historial as $evento)
                                    @php
                                        $borderColor = match ($evento->color_timeline) {
                                            'blue' => '#2563eb',
                                            'green' => '#059669',
                                            'purple' => '#7c3aed',
                                            'red' => '#dc2626',
                                            'cyan' => '#0891b2',
                                            'indigo' => '#4338ca',
                                            'teal' => '#0d9488',
                                            'amber' => '#d97706',
                                            'gray' => '#6b7280',
                                            default => '#6b7280',
                                        };
                                    @endphp
                                    <div class="mb-3 timeline-card" style="border-left: 4px solid {{ $borderColor }};">
                                        <div class="d-flex align-items-start justify-content-between mb-1">
                                            <p class="mb-0 fw-bold">{{ $evento->titulo }}</p>
                                            <small class="ms-3 text-muted text-nowrap">
                                                {{ $evento->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                        @if ($evento->descripcion)
                                            <p class="mb-1 text-muted small">{{ $evento->descripcion }}</p>
                                        @endif
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-1">
                                            <small class="text-muted">
                                                <i class="mdi-account-outline mdi"></i> {{ $evento->usuario->name }}
                                            </small>
                                            @if ($evento->estado_anterior && $evento->estado_nuevo)
                                                <div class="d-flex align-items-center gap-2">
                                                    @php
                                                        $bc1 = match ($evento->estado_anterior) {
                                                            'Pendiente' => 'bg-info',
                                                            'En Proceso' => 'bg-warning text-dark',
                                                            'Contratado' => 'bg-success',
                                                            'Cancelado' => 'bg-danger',
                                                            default => 'bg-secondary',
                                                        };
                                                        $bc2 = match ($evento->estado_nuevo) {
                                                            'Pendiente' => 'bg-info',
                                                            'En Proceso' => 'bg-warning text-dark',
                                                            'Contratado' => 'bg-success',
                                                            'Cancelado' => 'bg-danger',
                                                            default => 'bg-secondary',
                                                        };
                                                    @endphp
                                                    <span
                                                        class="badge {{ $bc1 }}">{{ $evento->estado_anterior }}</span>
                                                    <i class="mdi-arrow-right text-muted mdi"></i>
                                                    <span
                                                        class="badge {{ $bc2 }}">{{ $evento->estado_nuevo }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-4 text-center">
                                        <i class="d-block mb-2 mdi-timeline-text-outline text-muted mdi mdi-48px"></i>
                                        <p class="text-muted">No hay eventos registrados aún.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    {{-- SIDEBAR ACCIONES RRHH --}}
                    @if (auth()->user()->puedeGestionarRequerimientos() || auth()->user()->esGerenteGeneral() || $requerimiento->solicitante_id === auth()->id())
                        <div class="col-lg-4">

                            {{-- Mi Firma (aviso si no tiene) --}}
                            @if(!auth()->user()->tieneFirmaRegistrada())
                            <div class="shadow-sm mb-3 card" style="border-left:4px solid #ffc107 !important;">
                                <div class="card-body py-2">
                                    <p class="mb-1 small fw-semibold text-warning"><i class="mdi mdi-alert"></i> Sin firma registrada</p>
                                    <a href="{{ route('firma.index') }}" class="btn btn-sm btn-warning w-100">
                                        <i class="mdi mdi-draw"></i> Registrar mi firma
                                    </a>
                                </div>
                            </div>
                            @endif

                            {{-- Asignar Responsable --}}
                            @if(auth()->user()->puedeGestionarRequerimientos())
                            <div class="shadow-sm mb-3 card">
                                <div
                                    class="card-header {{ $requerimiento->estado === 'Pendiente' ? 'bg-primary text-white' : 'bg-light' }}">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-account-check"></i> Responsable RH
                                        @if ($requerimiento->estado === 'Pendiente')
                                            <span class="bg-warning ms-2 text-dark badge">Requerido para iniciar</span>
                                        @endif
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('rrhh.requerimientos.responsable', $requerimiento->id) }}"
                                        method="POST">
                                        @csrf @method('PATCH')
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">USUARIO INTERNO</label>
                                            <select name="responsable_rh_id" class="form-select-sm form-select">
                                                <option value="">Sin asignar</option>
                                                @foreach ($usuariosRrhh as $u)
                                                    <option value="{{ $u->id }}"
                                                        {{ $requerimiento->responsable_rh_id == $u->id ? 'selected' : '' }}>
                                                        {{ $u->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3 text-muted text-center small">— o empresa externa —</div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">EMPRESA / EXTERNO</label>
                                            <input type="text" name="responsable_rh_externo"
                                                class="form-control form-control-sm"
                                                value="{{ $requerimiento->responsable_rh_externo }}"
                                                placeholder="Ej: Chamba Talent">
                                        </div>
                                        <button type="submit"
                                            class="btn w-100 {{ $requerimiento->estado === 'Pendiente' ? 'btn-primary' : 'btn-outline-primary' }}">
                                            <i
                                                class="mdi mdi-{{ $requerimiento->estado === 'Pendiente' ? 'play-circle' : 'account-tie' }}"></i>
                                            {{ $requerimiento->estado === 'Pendiente' ? 'Asignar e Iniciar Proceso' : 'Reasignar Responsable' }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Cambiar Estado (solo En Proceso) --}}
                            @if ($requerimiento->estado === 'En Proceso')
                                <div class="shadow-sm mb-3 card">
                                    <div class="bg-light card-header">
                                        <h6 class="mb-0">
                                            <i class="mdi mdi-swap-horizontal"></i> Actualizar Estado
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('rrhh.requerimientos.estado', $requerimiento->id) }}"
                                            method="POST">
                                            @csrf @method('PATCH')
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">NUEVO ESTADO</label>
                                                <select name="estado" class="form-select-sm form-select">
                                                    <option value="En Proceso" selected>En Proceso</option>
                                                    <option value="Contratado">Contratado</option>
                                                    <option value="Cancelado">Cancelado</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="w-100 btn btn-primary">
                                                <i class="mdi-content-save mdi"></i> Guardar Estado
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Registrar Etapa --}}
                                <div class="shadow-sm mb-3 card">
                                    <div class="bg-light card-header">
                                        <h6 class="mb-0">
                                            <i class="mdi-clipboard-check-outline mdi"></i> Registrar Avance
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('rrhh.requerimientos.etapa', $requerimiento->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">ETAPA DEL PROCESO</label>
                                                <select name="tipo_etapa" class="form-select-sm form-select"
                                                    id="tipoEtapaSelect" required>
                                                    <option value="">Seleccionar etapa...</option>
                                                    @foreach ($etapas as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">DESCRIPCIÓN / DETALLE</label>
                                                <textarea name="descripcion" class="form-control form-control-sm" rows="4"
                                                    placeholder="Describe el avance de esta etapa..." maxlength="2000" required></textarea>
                                            </div>
                                            <button type="submit" class="w-100 btn btn-success">
                                                <i class="mdi mdi-send"></i> Registrar Avance
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                            @endif {{-- puedeGestionarRequerimientos --}}

                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .shadow-sm {
            box-shadow: 0 2px 15px rgba(0, 0, 0, .08) !important;
            border: none !important;
            border-radius: 10px !important;
        }

        .font-monospace {
            font-family: 'Courier New', monospace;
        }

        .card-stat {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        /* Timeline tarjeta con borde izquierdo */
        .timeline-card {
            background: #f8fafc;
            border-radius: 0 8px 8px 0;
            padding: .85rem 1rem;
            transition: background .15s;
        }

        .timeline-card:hover {
            background: #f0f4ff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const placeholders = {
            publicacion_oferta: 'Ej: Publicado en LinkedIn, Bumeran y Computrabajo...',
            revision_cvs: 'Ej: Se recibieron 24 CVs, se preseleccionaron 8 candidatos...',
            entrevista_virtual: 'Ej: Se realizaron entrevistas virtuales con 5 candidatos...',
            entrevista_presencial: 'Ej: Se citaron 3 candidatos a entrevista presencial...',
            evaluacion: 'Ej: Se aplicó evaluación psicológica y prueba técnica...',
            oferta_candidato: 'Ej: Se envió oferta formal a Juan Pérez. Sueldo: S/ 2,800...',
            en_capacitacion: 'Ej: El candidato inició capacitación el día...',
            nota: 'Escribe tu nota o comentario libre aquí...',
        };
        document.getElementById('tipoEtapaSelect')?.addEventListener('change', function() {
            const textarea = this.closest('form').querySelector('textarea[name="descripcion"]');
            if (textarea && placeholders[this.value]) textarea.placeholder = placeholders[this.value];
        });
    </script>
@endpush

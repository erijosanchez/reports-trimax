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
                                'Cancelado' => 'bg-secondary',
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
                            <span class="bg-secondary px-3 py-2 badge fs-6">Regular</span>
                        @endif
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

                    {{-- COLUMNA PRINCIPAL --}}
                    <div class="{{ auth()->user()->puedeGestionarRequerimientos() ? 'col-lg-8' : 'col-lg-12' }}">

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
                                            <h2 class="mb-0 {{ $kpiColor }}">{{ $requerimiento->kpi }}</h2>
                                            <small class="text-muted">días transcurridos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">TIEMPO TOTAL</p>
                                            <h2 class="mb-0 text-dark">{{ $requerimiento->tiempo_total }}</h2>
                                            <small class="text-muted">SLA + KPI</small>
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

                        {{-- HISTORIAL / TIMELINE --}}
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h6 class="mb-4 text-primary">
                                    <i class="mdi mdi-history"></i> Historial del Proceso
                                </h6>

                                @forelse($requerimiento->historial as $evento)
                                    <div class="d-flex gap-3 mb-4 timeline-item">
                                        {{-- Ícono --}}
                                        <div class="flex-shrink-0">
                                            <div class="timeline-icon-wrap timeline-icon-{{ $evento->color_timeline }}">
                                                <i class="mdi {{ $evento->icono_timeline }}"></i>
                                            </div>
                                        </div>
                                        {{-- Contenido --}}
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-start justify-content-between mb-1">
                                                <strong class="small">{{ $evento->usuario->name }}</strong>
                                                <small
                                                    class="text-muted">{{ $evento->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <p class="mb-1 fw-semibold">{{ $evento->titulo }}</p>
                                            @if ($evento->descripcion)
                                                <p class="mb-1 text-muted small">{{ $evento->descripcion }}</p>
                                            @endif
                                            @if ($evento->estado_anterior && $evento->estado_nuevo)
                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                    @php
                                                        $bc1 = match ($evento->estado_anterior) {
                                                            'Pendiente' => 'bg-info',
                                                            'En Proceso' => 'bg-warning text-dark',
                                                            'Contratado' => 'bg-success',
                                                            'Cancelado' => 'bg-secondary',
                                                            default => 'bg-secondary',
                                                        };
                                                        $bc2 = match ($evento->estado_nuevo) {
                                                            'Pendiente' => 'bg-info',
                                                            'En Proceso' => 'bg-warning text-dark',
                                                            'Contratado' => 'bg-success',
                                                            'Cancelado' => 'bg-secondary',
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
                    @if (auth()->user()->puedeGestionarRequerimientos())
                        <div class="col-lg-4">

                            {{-- Asignar Responsable --}}
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

        /* Timeline */
        .timeline-item+.timeline-item {
            border-top: 1px solid #f0f0f0;
            padding-top: 1rem;
        }

        .timeline-icon-wrap {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .timeline-icon-blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .timeline-icon-green {
            background: #d1fae5;
            color: #059669;
        }

        .timeline-icon-purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .timeline-icon-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .timeline-icon-cyan {
            background: #cffafe;
            color: #0891b2;
        }

        .timeline-icon-indigo {
            background: #e0e7ff;
            color: #4338ca;
        }

        .timeline-icon-teal {
            background: #ccfbf1;
            color: #0d9488;
        }

        .timeline-icon-amber {
            background: #fef3c7;
            color: #d97706;
        }

        .timeline-icon-gray {
            background: #f1f5f9;
            color: #64748b;
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
            nota: 'Escribe tu nota o comentario libre aquí...',
        };
        document.getElementById('tipoEtapaSelect')?.addEventListener('change', function() {
            const textarea = this.closest('form').querySelector('textarea[name="descripcion"]');
            if (textarea && placeholders[this.value]) textarea.placeholder = placeholders[this.value];
        });
    </script>
@endpush

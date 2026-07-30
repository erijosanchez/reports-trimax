@extends('layouts.app')

@section('title', 'Feriados')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-3">
                <h3 class="rate-percentage mb-0">Feriados Nacionales</h3>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="me-2 mdi mdi-check-circle"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="me-2 mdi mdi-alert-circle"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Sincronizar desde gob.pe --}}
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Sincronizar desde gob.pe</h4>
                    <p class="card-description">
                        Trae la lista de feriados nacionales publicada en
                        <strong>gob.pe/feriados</strong>. Primero pide la <strong>vista previa</strong>
                        (no guarda nada) y revisa que se vea bien antes de sincronizar de verdad.
                    </p>

                    <div class="d-flex gap-2 mb-2">
                        <input type="number" form="form-preview" name="anio" class="form-control" style="max-width:120px"
                               value="{{ $anio }}" min="2020" max="2100">
                        <form id="form-preview" action="{{ route('admin.feriados.preview') }}" method="POST">
                            @csrf
                            <input type="hidden" name="anio" value="{{ $anio }}">
                        </form>
                        <button type="submit" form="form-preview" class="btn btn-outline-primary">
                            <i class="me-1 mdi mdi-eye-outline"></i>Vista previa
                        </button>
                    </div>

                    @if (session('preview'))
                        <div class="alert alert-info small mb-2">
                            <strong>Vista previa {{ session('preview_anio') }} — nada se guardó todavía:</strong>
                            <pre class="mb-0 mt-1" style="white-space:pre-wrap;font-size:.8rem">{{ session('preview') }}</pre>
                        </div>
                        <form action="{{ route('admin.feriados.sync') }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <input type="hidden" name="anio" value="{{ session('preview_anio') }}">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="me-1 mdi mdi-cloud-download"></i>Aplicar esta sincronización
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Agregar manual --}}
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Agregar feriado manualmente</h4>
                    <form action="{{ route('admin.feriados.store') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small">Fecha</label>
                            <input type="date" name="fecha" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Motivo</label>
                            <input type="text" name="motivo" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="nacional" selected>Nacional</option>
                                <option value="regional">Regional</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="mdi mdi-plus"></i> Agregar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="card-title mb-0">Feriados {{ $anio }}</h4>
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <label class="small text-muted mb-0">Año:</label>
                            <select name="anio" class="form-select form-select-sm" style="width:110px" onchange="this.form.submit()">
                                @foreach ($aniosDisponibles as $a)
                                    <option value="{{ $a }}" {{ (int) $a === $anio ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                                @if (!$aniosDisponibles->contains($anio))
                                    <option value="{{ $anio }}" selected>{{ $anio }}</option>
                                @endif
                            </select>
                        </form>
                    </div>

                    @if ($feriados->isEmpty())
                        <p class="card-description mb-0">No hay feriados registrados para {{ $anio }}.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Día</th>
                                        <th>Motivo</th>
                                        <th>Tipo</th>
                                        <th>Fuente</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($feriados as $f)
                                    <tr>
                                        <td>{{ $f->fecha->format('d/m/Y') }}</td>
                                        <td class="text-capitalize">{{ $f->fecha->translatedFormat('l') }}</td>
                                        <td>{{ $f->motivo }}</td>
                                        <td>
                                            <span class="badge bg-{{ $f->tipo === 'nacional' ? 'primary' : 'info' }}">
                                                {{ ucfirst($f->tipo) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $f->fuente }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.feriados.destroy', $f->id) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar este feriado?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

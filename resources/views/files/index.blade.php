@extends('layouts.app')

@section('title', 'Archivos')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            <!-- Header -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h3 class="rate-percentage mb-0">Gestión de Archivos</h3>
                                            <p class="text-muted mt-1">Administra documentos PDF y Excel</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario de Subida -->
                            <div class="row mb-4">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-cloud-upload text-primary me-2"></i>Subir Nuevo Archivo
                                            </h4>
                                            <form method="POST" action="{{ route('files.upload') }}"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Archivo (PDF, Excel)</label>
                                                        <input type="file" name="file" class="form-control p-2" required
                                                            accept=".pdf,.xlsx,.xls,.csv">
                                                        <small class="text-muted">Formatos permitidos: PDF, XLSX, XLS,
                                                            CSV</small>
                                                    </div>

                                                    <div class="col-md-5 mb-3">
                                                        <label class="form-label">Descripción (opcional)</label>
                                                        <input type="text" name="description" class="form-control p-2"
                                                            placeholder="Escribe una breve descripción...">
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label d-block">&nbsp;</label>
                                                        <div class="form-check form-check-primary mb-2">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" name="is_public" value="1"
                                                                    class="form-check-input">
                                                                Archivo público
                                                                <i class="input-helper"></i>
                                                            </label>
                                                        </div>
                                                        <button type="submit" class="btn btn-success w-100">
                                                            <i class="mdi mdi-upload me-1"></i>Subir Archivo
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Archivos -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title">Archivos Disponibles</h4>
                                            <p class="card-description">
                                                Total de archivos: <code>{{ $files->total() }}</code>
                                            </p>

                                            @if ($files->isEmpty())
                                                <div class="text-center py-5">
                                                    <i
                                                        class="mdi mdi-file-document-box-outline mdi-48px text-muted mb-3 d-block"></i>
                                                    <h4 class="mb-3">No hay archivos disponibles</h4>
                                                    <p class="text-muted">Sube tu primer archivo usando el formulario de
                                                        arriba.</p>
                                                </div>
                                            @else
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                    <i class="mdi mdi-file-document me-1"></i>Nombre
                                                                </th>
                                                                <th>Tipo</th>
                                                                <th>Tamaño</th>
                                                                <th>Subido por</th>
                                                                <th>Fecha</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($files as $file)
                                                                <tr>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            @if ($file->file_type === 'pdf')
                                                                                <i
                                                                                    class="mdi mdi-file-pdf-box text-danger me-2 mdi-24px"></i>
                                                                            @else
                                                                                <i
                                                                                    class="mdi mdi-file-excel-box text-success me-2 mdi-24px"></i>
                                                                            @endif
                                                                            <div>
                                                                                <span
                                                                                    class="fw-bold">{{ $file->original_name }}</span>
                                                                                @if ($file->description)
                                                                                    <br><small
                                                                                        class="text-muted">{{ $file->description }}</small>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="badge badge-outline-primary">{{ strtoupper($file->file_type) }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="text-muted">{{ $file->file_size_formatted }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <img class="img-xs rounded-circle me-2"
                                                                                src="https://ui-avatars.com/api/?name={{ urlencode($file->user->name) }}&background=6366f1&color=fff"
                                                                                alt="profile">
                                                                            <span>{{ $file->user->name }}</span>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="text-muted">{{ $file->created_at->format('d/m/Y') }}</span><br>
                                                                        <small
                                                                            class="text-muted">{{ $file->created_at->format('H:i') }}</small>
                                                                    </td>
                                                                    <td>
                                                                        <div class="d-flex gap-1">
                                                                            <a href="{{ route('files.view', $file->id) }}"
                                                                                class="btn btn-sm btn-outline-primary"
                                                                                title="Ver archivo">
                                                                                <i class="mdi mdi-eye"></i>
                                                                            </a>
                                                                            <a href="{{ route('files.download', $file->id) }}"
                                                                                class="btn btn-sm btn-outline-success"
                                                                                title="Descargar">
                                                                                <i class="mdi mdi-download"></i>
                                                                            </a>
                                                                            @if ($file->user_id === auth()->id() || auth()->user()->isAdmin())
                                                                                <form method="POST"
                                                                                    action="{{ route('files.destroy', $file->id) }}"
                                                                                    style="display:inline;">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit"
                                                                                        onclick="return confirm('¿Estás seguro de eliminar este archivo?')"
                                                                                        class="btn btn-sm btn-outline-danger"
                                                                                        title="Eliminar">
                                                                                        <i class="mdi mdi-delete"></i>
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Paginación -->
                                                <div class="mt-4">
                                                    {{ $files->links() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Fix para gap en navegadores que no lo soporten */
        .d-flex.gap-1>*+* {
            margin-left: 0.25rem;
        }
    </style>
@endsection

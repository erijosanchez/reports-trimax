@extends('layouts.app')

@section('title', 'Historial de Ubicaciones')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    </div>
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between statistics-details">
                                        <div>
                                            <h3 class="rate-percentage">Historial de Ubicaciones</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros -->
                            <div class="row mt-1">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">Filtros de Búsqueda</h4>
                                            <form method="GET">
                                                <div class="row">
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Usuario</label>
                                                        <select name="user_id" class="form-select">
                                                            <option value="">Todos</option>
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}"
                                                                    {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                                    {{ $user->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Ciudad</label>
                                                        <input type="text" name="city" value="{{ request('city') }}"
                                                            placeholder="Buscar ciudad" class="form-control">
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Desde</label>
                                                        <input type="date" name="date_from"
                                                            value="{{ request('date_from') }}" class="form-control">
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Hasta</label>
                                                        <input type="date" name="date_to"
                                                            value="{{ request('date_to') }}" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary text-white">
                                                        <i class="mdi mdi-filter me-1"></i>Filtrar
                                                    </button>
                                                    <a href="{{ route('admin.locations.index') }}" class="btn btn-light">
                                                        <i class="mdi mdi-refresh me-1"></i>Limpiar
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Ubicaciones -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title">Registros de Ubicación</h4>
                                            <p class="card-description">
                                                Total de registros: <code>{{ $locations->total() }}</code>
                                            </p>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha/Hora</th>
                                                            <th>Usuario</th>
                                                            <th>Ubicación</th>
                                                            <th>IP</th>
                                                            <th>Coordenadas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($locations as $location)
                                                            <tr>
                                                                <td>
                                                                    <span
                                                                        class="text-muted">{{ $location->created_at->format('d/m/Y') }}</span><br>
                                                                    <small
                                                                        class="text-muted">{{ $location->created_at->format('H:i:s') }}</small>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="img-xs rounded-circle me-2"
                                                                            src="https://ui-avatars.com/api/?name={{ urlencode($location->user->name) }}&background=6366f1&color=fff"
                                                                            alt="profile">
                                                                        <span>{{ $location->user->name }}</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="mdi mdi-map-marker text-primary me-2"></i>
                                                                        <span>{{ $location->formatted_location }}</span>
                                                                    </div>
                                                                    @if ($location->is_vpn)
                                                                        <span class="badge badge-danger mt-1">VPN</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <code
                                                                        class="text-dark">{{ $location->ip_address }}</code>
                                                                </td>
                                                                <td>
                                                                    @if ($location->latitude && $location->longitude)
                                                                        <small class="text-muted">
                                                                            {{ number_format($location->latitude, 4) }},
                                                                            {{ number_format($location->longitude, 4) }}
                                                                        </small>
                                                                    @else
                                                                        <span class="text-muted">N/A</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center py-5">
                                                                    <div class="text-muted">
                                                                        <i
                                                                            class="mdi mdi-map-marker-off mdi-48px d-block mb-2"></i>
                                                                        <p>No se encontraron ubicaciones</p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Paginación -->
                                            <div class="mt-4">
                                                {{ $locations->links() }}
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
    </div>
@endsection

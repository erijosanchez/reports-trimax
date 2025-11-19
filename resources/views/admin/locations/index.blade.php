@extends('layouts.app')

@section('title', 'Historial de Ubicaciones GPS')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom"></div>
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            
                            <!-- TÍTULO -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between statistics-details">
                                        <div>
                                            <h3 class="rate-percentage"><i class="mdi mdi-history"></i> Historial de Ubicaciones</h3>
                                            <p style="color:#666;">Registro completo de ubicaciones precisas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FILTROS -->
                            <div class="row mt-1">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4"><i class="mdi mdi-filter me-1"></i> Filtros de Búsqueda</h4>
                                            <form method="GET" action="{{ route('admin.locations.index') }}">
                                                <div class="row">
                                                    <!-- Usuario -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Usuario</label>
                                                        <select name="user_id" class="form-select">
                                                            <option value="">Todos los usuarios</option>
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}"
                                                                    {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                                    {{ $user->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Ciudad -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Ciudad</label>
                                                        <input type="text" 
                                                               name="city" 
                                                               value="{{ request('city') }}"
                                                               placeholder="Ej: Lima, Arequipa" 
                                                               class="form-control">
                                                    </div>

                                                    <!-- Fecha Desde -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Desde</label>
                                                        <input type="date" 
                                                               name="date_from"
                                                               value="{{ request('date_from') }}" 
                                                               class="form-control">
                                                    </div>

                                                    <!-- Fecha Hasta -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Hasta</label>
                                                        <input type="date" 
                                                               name="date_to"
                                                               value="{{ request('date_to') }}" 
                                                               class="form-control">
                                                    </div>
                                                </div>

                                                <!-- Botones -->
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="mdi mdi-filter me-1"></i>Filtrar
                                                    </button>
                                                    <a href="{{ route('admin.locations.index') }}" class="btn btn-light">
                                                        <i class="mdi mdi-refresh me-1"></i>Limpiar
                                                    </a>
                                                    <a href="{{ route('admin.locations.map') }}" class="btn btn-success">
                                                        <i class="mdi mdi-map me-1"></i>Ver Mapa
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TABLA DE UBICACIONES -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h4 class="card-title mb-0">Registros GPS</h4>
                                                    <p class="card-description">
                                                        Total: <code>{{ $locations->total() }}</code> ubicaciones
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>📅 Fecha/Hora</th>
                                                            <th>👤 Usuario</th>
                                                            <th>📍 Ubicación GPS</th>
                                                            <th>🎯 Precisión</th>
                                                            <th>📐 Coordenadas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($locations as $location)
                                                            <tr>
                                                                <!-- Fecha/Hora -->
                                                                <td>
                                                                    <div>
                                                                        <strong>{{ $location->created_at->format('d/m/Y') }}</strong>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        {{ $location->created_at->format('H:i:s') }}
                                                                    </small>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        {{ $location->created_at->diffForHumans() }}
                                                                    </small>
                                                                </td>

                                                                <!-- Usuario -->
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="img-xs rounded-circle me-2"
                                                                            src="https://ui-avatars.com/api/?name={{ urlencode($location->user->name) }}&background=6366f1&color=fff"
                                                                            alt="{{ $location->user->name }}">
                                                                        <div>
                                                                            <div>{{ $location->user->name }}</div>
                                                                            <small class="text-muted">{{ $location->user->email }}</small>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <!-- Ubicación GPS -->
                                                                <td>
                                                                    <div class="d-flex align-items-start">
                                                                        <i class="mdi mdi-cellphone-marker text-success me-2" style="font-size:1.3rem;"></i>
                                                                        <div>
                                                                            @if($location->formatted_address)
                                                                                <div style="font-size:0.95rem;">
                                                                                    {{ $location->formatted_address }}
                                                                                </div>
                                                                            @elseif($location->city)
                                                                                <div>
                                                                                    @if($location->street_name)
                                                                                        {{ $location->street_name }}
                                                                                        @if($location->street_number)
                                                                                            #{{ $location->street_number }}
                                                                                        @endif
                                                                                        <br>
                                                                                    @endif
                                                                                    @if($location->district)
                                                                                        {{ $location->district }},
                                                                                    @endif
                                                                                    {{ $location->city }}
                                                                                </div>
                                                                            @else
                                                                                <span class="text-muted">Ubicación no disponible</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <!-- Precisión -->
                                                                <td>
                                                                    @if($location->accuracy)
                                                                        <span class="badge" style="background:
                                                                            @if($location->accuracy < 50) #28a745
                                                                            @elseif($location->accuracy < 100) #5cb85c
                                                                            @elseif($location->accuracy < 500) #ffc107
                                                                            @else #dc3545
                                                                            @endif
                                                                        ;color:white;padding:0.4rem 0.8rem;">
                                                                            {{ number_format($location->accuracy, 0) }}m
                                                                        </span>
                                                                        <br>
                                                                        <small class="text-muted">
                                                                            @if($location->accuracy < 50)
                                                                                ⭐ Excelente
                                                                            @elseif($location->accuracy < 100)
                                                                                ✓ Buena
                                                                            @elseif($location->accuracy < 500)
                                                                                △ Regular
                                                                            @else
                                                                                ○ Baja
                                                                            @endif
                                                                        </small>
                                                                    @else
                                                                        <span class="text-muted">N/A</span>
                                                                    @endif
                                                                </td>

                                                                <!-- Coordenadas -->
                                                                <td>
                                                                    @if($location->latitude && $location->longitude)
                                                                        <code style="font-size:0.85rem;">
                                                                            {{ number_format($location->latitude, 6) }},<br>
                                                                            {{ number_format($location->longitude, 6) }}
                                                                        </code>
                                                                    @else
                                                                        <span class="text-muted">N/A</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center py-5">
                                                                    <div class="text-muted">
                                                                        <i class="mdi mdi-map-marker-off" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                                                                        <p style="font-size:1.1rem;">No se encontraron ubicaciones GPS</p>
                                                                        <small>Intenta ajustar los filtros de búsqueda</small>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Paginación -->
                                            @if($locations->hasPages())
                                                <div class="mt-4">
                                                    {{ $locations->appends(request()->query())->links() }}
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
@endsection

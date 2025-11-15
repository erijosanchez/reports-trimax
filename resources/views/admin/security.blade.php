@extends('layouts.app')

@section('title', 'Seguridad y Analisis')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    </div>
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="statistics-details d-flex align-items-center justify-content-between">
                                        <div>
                                            <h3 class="rate-percentage">Seguridad & Analisis</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">IPs Bloqueadas ({{ $blockedIps->total() }})</h4>
                        @if ($blockedIps->isEmpty())
                            <p class="card-description">
                                No hay IPs bloqueadas actualmente.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>IP</th>
                                            <th>Razón</th>
                                            <th>Bloqueado Hasta</th>
                                            <th>Fecha de Bloqueo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($blockedIps as $ip)
                                            <tr>
                                                <td>{{ $ip->ip_address }}</td>
                                                <td>{{ $ip->reason }}</td>
                                                <td>
                                                    @if ($ip->blocked_until)
                                                        {{ $ip->blocked_until->format('d/m/Y H:i') }}
                                                    @else
                                                        <label class="badge badge-danger">Permanente</label>
                                                    @endif
                                                </td>
                                                <td>{{ $ip->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <!-- INTENTOS FALLIDOS -->
            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Intentos de Login Fallidos Recientes</h4>
                        @if ($recentFailedAttempts->isEmpty())
                            <p class="card-description">
                                No hay intentos fallidos recientes.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Email</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentFailedAttempts as $attempt)
                                            <tr>
                                                <td>{{ $attempt->attempted_at->format('d/m/Y H:i:s') }}</td>
                                                <td>{{ $attempt->email }}</td>
                                                <td class="text-danger">{{ $attempt->ip_address }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ANALITICS -->
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Top 10 Usuarios por Tiempo de Uso</h4>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            Usuario
                                        </th>
                                        <th>
                                            Tiempo Total
                                        </th>
                                        <th>
                                            Sesiones
                                        </th>
                                        <th>
                                            Promedio por Sesión
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($usageStats as $stat)
                                        <tr>
                                            <td class="py-1">
                                                {{ $stat['name'] }}
                                            </td>
                                            <td>
                                                {{ floor($stat['total_time'] / 3600) }}h
                                                {{ floor(($stat['total_time'] % 3600) / 60) }}m
                                            </td>
                                            <td>
                                                {{ $stat['sessions_count'] }}
                                            </td>
                                            <td>
                                                @if ($stat['sessions_count'] > 0)
                                                    {{ floor($stat['total_time'] / $stat['sessions_count'] / 60) }}m
                                                @else
                                                    0m
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Gestionar Usuarios')

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
                                            <h3 class="rate-percentage">Gestión de Usuarios</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title m-0">Gestionar Usuarios</h4>
                            <a class="btn btn-rounded btn-primary p-2" href="{{ route('admin.users.create') }}"><i
                                    class="mdi mdi-account-plus fs-4"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Sesiones</th>
                                        <th>Ultimo Login</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <label class="badge badge-primary">
                                                        {{ $role->name }}
                                                    </label>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if ($user->is_active)
                                                    <label class="badge badge-success">✓ Activo</label>
                                                @else
                                                    <label class="badge badge-danger">✗ Inactivo</label>
                                                @endif
                                            </td>
                                            <td>{{ $user->sessions_count }}</td>
                                            <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca' }}
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                                    class="btn btn-warning p-2"><i class="mdi mdi-pencil fs-5"></i></a>
                                                @if ($user->id !== auth()->id())
                                                    <form method="POST"
                                                        action="{{ route('admin.users.destroy', $user->id) }}"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            onclick="return confirm('¿Eliminar usuario?')"
                                                            class="btn btn-danger p-2"><i
                                                                class="mdi mdi-delete fs-5 "></i></button>
                                                    </form>
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
            <div class="col-sm-4 d-flex flex-column">
                <div class="row flex-grow">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card card-rounded">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <h4 class="card-title card-title-dash">Usuarios Online
                                                    ({{ $usersOnline->count() }})</h4>
                                            </div>
                                        </div>
                                        <p style="color:#666;margin-top:0.5rem;">Usuarios activos en los últimos 5 minutos
                                        </p>

                                        @if ($usersOnline->isEmpty())
                                            <p style="margin-top:2rem;">No hay usuarios online en este momento.</p>
                                        @else
                                            @foreach ($usersOnline as $user)
                                                @foreach ($user->activeSessions as $session)
                                                    <div class="mt-3">
                                                        <div
                                                            class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                                            <div class="d-flex">
                                                                <div class="position-relative">
                                                                    <img class="img-sm rounded-circle"
                                                                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&"
                                                                        alt="profile">
                                                                    <span class="online-indicator pulse"></span>
                                                                </div>
                                                                <div class="wrapper ms-3">
                                                                    <p class="ms-1 mb-1 fw-bold">{{ $user->name }}</p>
                                                                    <small class="text-muted mb-0">{{ $session->last_activity->diffForHumans() }}</small>
                                                                </div>
                                                            </div>
                                                            <div class="text-muted text-small">
                                                                {{ $session->login_at->diffForHumans(null, true) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endforeach
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
@endsection

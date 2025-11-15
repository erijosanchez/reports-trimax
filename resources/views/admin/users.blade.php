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
            <div class="col-sm-12 grid-margin stretch-card">
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
                                                            class="btn btn-danger p-2"><i class="mdi mdi-delete fs-5 "></i></button>
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
        </div>
    </div>
@endsection

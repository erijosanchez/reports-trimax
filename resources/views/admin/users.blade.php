@extends('layouts.app')

@section('title', 'Gestionar Usuarios')

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
        <h1>Gestionar Usuarios</h1>
        <a href="{{ route('admin.users.create') }}"
            style="padding:0.5rem 1rem;background:#28a745;color:white;text-decoration:none;border-radius:4px;">
            + Crear Usuario
        </a>
    </div>

    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Nombre</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Email</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Rol</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Estado</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Sesiones</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Último Login</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td style="padding:0.75rem;border:1px solid #ddd;">{{ $user->name }}</td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">{{ $user->email }}</td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        @foreach ($user->roles as $role)
                            <span
                                style="padding:0.25rem 0.5rem;background:#007bff;color:white;border-radius:3px;font-size:0.85rem;">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        @if ($user->is_active)
                            <span style="color:#28a745;">✓ Activo</span>
                        @else
                            <span style="color:#dc3545;">✗ Inactivo</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">{{ $user->sessions_count }}</td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca' }}
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        <a href="{{ route('admin.users.edit', $user->id) }}"
                            style="color:#007bff;margin-right:0.5rem;">Editar</a>
                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('¿Eliminar usuario?')"
                                    style="background:none;border:none;color:#dc3545;cursor:pointer;">Eliminar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:1rem;">
        {{ $users->links() }}
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Archivos')

@section('content')
    <h1>Archivos</h1>

    <div style="margin-top:2rem;border:1px solid #ddd;padding:1.5rem;border-radius:4px;background:#f9f9f9;">
        <h3>Subir Nuevo Archivo</h3>
        <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:flex;gap:1rem;align-items:end;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <label>Archivo (PDF, Excel):</label>
                    <input type="file" name="file" required accept=".pdf,.xlsx,.xls,.csv"
                        style="width:100%;margin-top:0.25rem;">
                </div>
                <div style="flex:1;min-width:200px;">
                    <label>Descripción (opcional):</label>
                    <input type="text" name="description" style="width:100%;padding:0.5rem;margin-top:0.25rem;">
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="is_public" value="1"> Público
                    </label>
                </div>
                <button type="submit"
                    style="padding:0.5rem 1.5rem;background:#28a745;color:white;border:none;cursor:pointer;border-radius:4px;">
                    Subir
                </button>
            </div>
        </form>
    </div>

    <div style="margin-top:2rem;">
        @if ($files->isEmpty())
            <p>No hay archivos.</p>
        @else
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Nombre</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Tipo</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Tamaño</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Subido por</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Fecha</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($files as $file)
                        <tr>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $file->original_name }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ strtoupper($file->file_type) }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $file->file_size_formatted }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $file->user->name }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $file->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">
                                <a href="{{ route('files.view', $file->id) }}"
                                    style="color:#007bff;margin-right:0.5rem;">Ver</a>
                                <a href="{{ route('files.download', $file->id) }}"
                                    style="color:#28a745;margin-right:0.5rem;">Descargar</a>
                                @if ($file->user_id === auth()->id() || auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('files.destroy', $file->id) }}"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('¿Eliminar?')"
                                            style="background:none;border:none;color:#dc3545;cursor:pointer;">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:1rem;">
                {{ $files->links() }}
            </div>
        @endif
    </div>
@endsection

@extends('layouts.app')

@section('title', $file->original_name)

@section('content')
    <h1>{{ $file->original_name }}</h1>

    <div style="margin-top:1rem;padding:1rem;background:#f9f9f9;border:1px solid #ddd;border-radius:4px;">
        <p><strong>Tipo:</strong> {{ strtoupper($file->file_type) }}</p>
        <p><strong>Tamaño:</strong> {{ $file->file_size_formatted }}</p>
        <p><strong>Subido por:</strong> {{ $file->user->name }}</p>
        <p><strong>Fecha:</strong> {{ $file->created_at->format('d/m/Y H:i') }}</p>
        @if ($file->description)
            <p><strong>Descripción:</strong> {{ $file->description }}</p>
        @endif
    </div>

    <div style="margin-top:2rem;">
        @if ($file->file_type === 'pdf')
            <iframe src="data:application/pdf;base64,{{ base64_encode($content) }}" width="100%" height="800"
                style="border:1px solid #ddd;">
            </iframe>
        @else
            <p>Vista previa no disponible para este tipo de archivo.</p>
        @endif
    </div>

    <div style="margin-top:1rem;">
        <a href="{{ route('files.download', $file->id) }}"
            style="padding:0.5rem 1rem;background:#28a745;color:white;text-decoration:none;border-radius:4px;">
            Descargar Archivo
        </a>
        <a href="{{ route('files.index') }}" style="margin-left:1rem;color:#007bff;">
            ← Volver
        </a>
    </div>
@endsection

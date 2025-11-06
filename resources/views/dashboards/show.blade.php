@extends('layouts.app')

@section('title', $dashboard->name)

@section('content')
    <h1>{{ $dashboard->name }}</h1>
    <p>{{ $dashboard->description }}</p>

    <div style="margin-top:2rem;border:1px solid #ddd;border-radius:4px;overflow:hidden;">
        <iframe src="{{ $powerbiLink }}" width="100%" height="600" frameborder="0" allowfullscreen>
        </iframe>
    </div>

    <a href="{{ route('dashboards.index') }}" style="display:inline-block;margin-top:1rem;color:#007bff;">
        ← Volver a Dashboards
    </a>
@endsection

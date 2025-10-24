@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<h1>Mis Dashboards</h1>

@if($dashboards->isEmpty())
    <p>No tienes acceso a ningún dashboard.</p>
@else
    @foreach($dashboards as $dashboard)
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <h3>{{ $dashboard->title }}</h3>
            <p>{{ $dashboard->description }}</p>
            <a href="{{ route('dashboard.show', $dashboard->id) }}">Ver Dashboard</a>
        </div>
    @endforeach
@endif
@endsection
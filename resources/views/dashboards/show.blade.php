@extends('layouts.app')

@section('title', $dashboard->title)

@section('others')

    <div class="page-header">
        <div class="breadcrumb">
            <span>Admin</span> / <a href="{{ route('admin.dashboards.index') }}">Dashboards</a> / {{ $dashboard->title }}
        </div>
        <div
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
            <div style="flex: 1 1 auto; min-width: 200px;">
                <h1 style="color: #0a2540;">{{ $dashboard->title }}</h1>
                <p style="color: #666; font-size: 14px;">
                    {{ $dashboard->description ?? 'Visualiza los datos de tu reporte en Power BI' }}</p>
            </div>
            <a href="{{ route('admin.dashboards.index') }}" class="btn btn-outline" style="white-space: nowrap;">
                ← Volver
            </a>
        </div>
    </div>

    <div class="content-area">
        <div class="card" style="padding: 20px;">
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <h2 style="color: #0a2540; font-size: 20px;">Vista del Dashboard</h2>
                <div class="iframe-container">
                    <iframe src="{{ $dashboard->embed_url }}" frameborder="0" allowfullscreen="true"></iframe>
                </div>
            </div>
        </div>
    </div>

@endsection

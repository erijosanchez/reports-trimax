@extends('layouts.app')

@section('title', 'Inicio')

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
                                            <h3 class="rate-percentage">Tus Dashboards</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if ($dashboards->isEmpty())
                                    <p>No tienes dashboards asignados.</p>
                                @else
                                    @foreach ($dashboards as $dashboard)
                                        <div class="col-lg-4 d-flex flex-column">
                                            <div class="row flex-grow">
                                                <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                                                    <div class="card bg-primary card-rounded">
                                                        <div class="card-body pb-0">
                                                            <h4 class="card-title card-title-dash text-white mb-4">
                                                                {{ $dashboard->name }}
                                                            </h4>
                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <p class="status-summary-ight-white mb-1">
                                                                        {{ $dashboard->description }}
                                                                    </p>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="status-summary-chart-wrapper pb-4">
                                                                        <a href="{{ route('dashboards.show', $dashboard->id) }}"
                                                                            style="display:inline-block;margin-top:0.5rem;padding:0.5rem 1rem;background:#007bff;color:white;text-decoration:none;border-radius:4px;">
                                                                            Ver Dashboard
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

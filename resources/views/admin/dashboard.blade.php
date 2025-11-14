@extends('layouts.app')

@section('title', 'Panel Admin')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    </div>
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between statistics-details">
                                        <div>
                                            <h3 class="rate-percentage">Panel de Administración</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="d-flex flex-column col-lg-3">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-primary card-rounded card">
                                                <div class="pb-0 card-body">
                                                    <h4 class="mb-4 text-white card-title card-title-dash">Usuarios Totales
                                                    </h4>
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <p class="mb-1 status-summary-ight-white">Total</p>
                                                            <h2 class="text-info">{{ $stats['total_users'] }}</h2>
                                                        </div>
                                                        <div class="col-sm-8">
                                                            <div class="pb-4 status-summary-chart-wrapper">
                                                                <canvas id="status-summary"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--1-->
                                <div class="d-flex flex-column col-lg-3">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-primary card-rounded card">
                                                <div class="pb-0 card-body">
                                                    <h4 class="mb-4 text-white card-title card-title-dash">Usuarios Online
                                                    </h4>
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <p class="mb-1 status-summary-ight-white">Total</p>
                                                            <h2 class="text-info">{{ $stats['users_online'] }}</h2>
                                                        </div>
                                                        <div class="col-sm-8">
                                                            <div class="pb-4 status-summary-chart-wrapper">
                                                                <canvas id="status-summary"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--3-->
                                <div class="d-flex flex-column col-lg-3">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-primary card-rounded card">
                                                <div class="pb-0 card-body">
                                                    <h4 class="mb-4 text-white card-title card-title-dash">Sesiones Hoy
                                                    </h4>
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <p class="mb-1 status-summary-ight-white">Total</p>
                                                            <h2 class="text-info">{{ $stats['total_sessions_today'] }}</h2>
                                                        </div>
                                                        <div class="col-sm-8">
                                                            <div class="pb-4 status-summary-chart-wrapper">
                                                                <canvas id="status-summary"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--4-->
                                <div class="d-flex flex-column col-lg-3">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-primary card-rounded card">
                                                <div class="pb-0 card-body">
                                                    <h4 class="mb-4 text-white card-title card-title-dash">IPs Bloqueadas
                                                    </h4>
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <p class="mb-1 status-summary-ight-white">Total</p>
                                                            <h2 class="text-info">{{ $stats['blocked_ips'] }}</h2>
                                                        </div>
                                                        <div class="col-sm-8">
                                                            <div class="pb-4 status-summary-chart-wrapper">
                                                                <canvas id="status-summary"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="d-flex flex-column col-lg-8">

                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-body">
                                                    <div class="d-sm-flex align-items-start justify-content-between">
                                                        <div>
                                                            <h4 class="card-title card-title-dash">Actividad Reciente</h4>
                                                        </div>
                                                    </div>
                                                    <div class="table-responsive mt-1">
                                                        <table class="table select-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Usuario</th>
                                                                    <th>Acción</th>
                                                                    <th>Descripción</th>
                                                                    <th>Fecha</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($recentActivity as $log)
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex">
                                                                                <h6>{{ $log->user->name }}</h6>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <h6>{{ $log->action }}</h6>
                                                                        </td>
                                                                        <td>
                                                                            <p>{{ $log->description }}</p>
                                                                        </td>
                                                                        <td>
                                                                            <div class="badge-opacity-warning badge">
                                                                                {{ $log->created_at->diffForHumans() }}
                                                                            </div>
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
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-12 col-lg-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-rounded card-body">
                                                    <h4 class="card-title card-title-dash">Recent Events</h4>
                                                    <div class="align-items-center py-2 border-bottom list">
                                                        <div class="w-100 wrapper">
                                                            <p class="mb-2 font-weight-medium">
                                                                Change in Directors
                                                            </p>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="me-1 text-muted mdi mdi-calendar"></i>
                                                                    <p class="mb-0 text-muted text-small">Mar 14, 2019</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="align-items-center py-2 border-bottom list">
                                                        <div class="w-100 wrapper">
                                                            <p class="mb-2 font-weight-medium">
                                                                Other Events
                                                            </p>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="me-1 text-muted mdi mdi-calendar"></i>
                                                                    <p class="mb-0 text-muted text-small">Mar 14, 2019</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="align-items-center py-2 border-bottom list">
                                                        <div class="w-100 wrapper">
                                                            <p class="mb-2 font-weight-medium">
                                                                Quarterly Report
                                                            </p>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="me-1 text-muted mdi mdi-calendar"></i>
                                                                    <p class="mb-0 text-muted text-small">Mar 14, 2019</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="align-items-center py-2 border-bottom list">
                                                        <div class="w-100 wrapper">
                                                            <p class="mb-2 font-weight-medium">
                                                                Change in Directors
                                                            </p>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="me-1 text-muted mdi mdi-calendar"></i>
                                                                    <p class="mb-0 text-muted text-small">Mar 14, 2019</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="align-items-center pt-3 list">
                                                        <div class="w-100 wrapper">
                                                            <p class="mb-0">
                                                                <a href="#" class="text-primary fw-bold">Show all <i
                                                                        class="mdi-arrow-right ms-2 mdi"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin co-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-rounded card-body">
                                                    <h4 class="card-title card-title-dash">Usuarios Online Ahora</h4>
                                                    @if ($usersOnline->isEmpty())
                                                        <h4 class="card-title card-title-dash">No hay usuarios en linea
                                                        </h4>
                                                    @else
                                                        @foreach ($usersOnline as $user)
                                                            @foreach ($user->activeSessions as $session)
                                                                <!----->
                                                                @php
                                                                    $count = 0;
                                                                @endphp
                                                                <!-- --->
                                                                @if ($count < 5)
                                                                    <div
                                                                        class="align-items-center py-2 border-bottom list">
                                                                        <div
                                                                            class="d-flex justify-content-between w-100 wrapper">
                                                                            <p class="mb-2 font-weight-medium">
                                                                                {{ $user->name }}
                                                                            </p>
                                                                            <p class="mb-2 font-weight-medium">
                                                                                {{ $session->login_at->diffForHumans(null, true) }}
                                                                            </p>
                                                                            <div class="rounded-circle badge-success badge"
                                                                                style="width:10px; height:10px; padding:0; background-color: #28a745;">
                                                                            </div>
                                                                            
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    {{-- Ocultos al inicio --}}
                                                                    <div
                                                                        class="align-items-center py-2 border-bottom list extra-user d-none">
                                                                        <div
                                                                            class="d-flex justify-content-between w-100 wrapper">
                                                                            <p class="mb-2 font-weight-medium">
                                                                                {{ $user->name }}
                                                                            </p>
                                                                            <div class="rounded-circle badge-success badge"
                                                                                style="width:10px; height:10px; padding:0; background-color: #28a745;">
                                                                            </div>
                                                                            
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @php $count++; @endphp
                                                            @endforeach
                                                        @endforeach
                                                    @endif

                                                    @if ($count > 5)
                                                        <div class="align-items-center pt-3 list">
                                                            <div class="w-100 wrapper">
                                                                <p class="mb-0">
                                                                    <a href="#" id="showAllBtn"
                                                                        class="text-primary fw-bold">
                                                                        Show all <i class="mdi-arrow-right ms-2 mdi"></i>
                                                                    </a>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                        <h4 class="card-title card-title-dash">Activities</h4>
                                                        <p class="mb-0">20 finished, 5 remaining</p>
                                                    </div>
                                                    <ul class="bullet-line-list">
                                                        <li>
                                                            <div class="d-flex justify-content-between">
                                                                <div><span class="text-light-green">Ben Tossell</span>
                                                                    assign you a task</div>
                                                                <p>Just now</p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="d-flex justify-content-between">
                                                                <div><span class="text-light-green">Oliver Noah</span>
                                                                    assign you a task</div>
                                                                <p>1h</p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="d-flex justify-content-between">
                                                                <div><span class="text-light-green">Jack William</span>
                                                                    assign you a task</div>
                                                                <p>1h</p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="d-flex justify-content-between">
                                                                <div><span class="text-light-green">Leo Lucas</span> assign
                                                                    you a task</div>
                                                                <p>1h</p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="d-flex justify-content-between">
                                                                <div><span class="text-light-green">Thomas Henry</span>
                                                                    assign you a task</div>
                                                                <p>1h</p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="d-flex justify-content-between">
                                                                <div><span class="text-light-green">Ben Tossell</span>
                                                                    assign you a task</div>
                                                                <p>1h</p>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="d-flex justify-content-between">
                                                                <div><span class="text-light-green">Ben Tossell</span>
                                                                    assign you a task</div>
                                                                <p>1h</p>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <div class="align-items-center pt-3 list">
                                                        <div class="w-100 wrapper">
                                                            <p class="mb-0">
                                                                <a href="#" class="text-primary fw-bold">Show all <i
                                                                        class="mdi-arrow-right ms-2 mdi"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="d-flex align-items-center justify-content-between mb-3">
                                                                <div>
                                                                    <h4 class="card-title card-title-dash">Leave Report
                                                                    </h4>
                                                                </div>
                                                                <div>
                                                                    <div class="dropdown">
                                                                        <button
                                                                            class="me-0 mb-0 btn btn-secondary dropdown-toggle toggle-dark btn-lg"
                                                                            type="button" id="dropdownMenuButton3"
                                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                                            aria-expanded="false"> Month Wise </button>
                                                                        <div class="dropdown-menu"
                                                                            aria-labelledby="dropdownMenuButton3">
                                                                            <h6 class="dropdown-header">week Wise</h6>
                                                                            <a class="dropdown-item" href="#">Year
                                                                                Wise</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="mt-3">
                                                                <canvas id="leaveReport"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('showAllBtn')?.addEventListener('click', function(e) {
            e.preventDefault();

            document.querySelectorAll('.extra-user').forEach(function(item) {
                item.classList.remove('d-none');
            });

            // Oculta el link de "Show all"
            this.parentElement.parentElement.parentElement.classList.add('d-none');
        });
    </script>

@endsection


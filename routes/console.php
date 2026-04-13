<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\AlertaSlaRequerimientosJob;
use App\Jobs\AlertaCobranzaVencimientoJob;
use App\Jobs\AlertaCajaChicaVencimientoJob;
use App\Jobs\AlertaComentariosVencimientoJob;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new AlertaSlaRequerimientosJob)
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

// Alerta Depósito de Efectivo: lunes–sábado 11:00 AM (Lima) — 1h antes del límite diario (12:00 PM).
// Solo dispara correo si el usuario aún no envió el reporte de HOY (anti-spam por check interno).
Schedule::job(new AlertaCobranzaVencimientoJob)
    ->dailyAt('11:00')
    ->days([Schedule::MONDAY, Schedule::TUESDAY, Schedule::WEDNESDAY, Schedule::THURSDAY, Schedule::FRIDAY, Schedule::SATURDAY])
    ->withoutOverlapping()
    ->onOneServer()
    ->timezone('America/Lima');

// Alerta Caja Chica: sábado 7:00 PM (Lima) — dentro del horario laboral, 5h antes del límite (11:59 PM).
Schedule::job(new AlertaCajaChicaVencimientoJob)
    ->weeklyOn(6, '19:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->timezone('America/Lima');

// Alerta Comentarios: jueves 7:00 PM (Lima) — dentro del horario laboral, 5h antes del límite (11:59 PM).
Schedule::job(new AlertaComentariosVencimientoJob)
    ->weeklyOn(4, '19:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->timezone('America/Lima');

Schedule::command('trimax:sync-ordenes-sede')
    ->everyFifteenMinutes()
    ->withoutOverlapping(20)
    ->runInBackground();

Schedule::command('trimax:sync-asignacion-bases')
    ->everyFifteenMinutes()
    ->withoutOverlapping(20)
    ->runInBackground();
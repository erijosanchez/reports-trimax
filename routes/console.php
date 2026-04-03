<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\AlertaSlaRequerimientosJob;
use App\Jobs\AlertaCobranzaVencimientoJob;
use App\Jobs\AlertaCajaChicaVencimientoJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new AlertaSlaRequerimientosJob)
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

// Alerta de cobranza: sábado a las 11:00 AM (Lima) — 1 hora antes del límite
Schedule::job(new AlertaCobranzaVencimientoJob)
    ->weeklyOn(6, '11:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->timezone('America/Lima');

// Alerta Caja Chica: sábado 1:00 PM (Lima) — 1 hora antes del límite (2:00 PM)
Schedule::job(new AlertaCajaChicaVencimientoJob)
    ->weeklyOn(6, '13:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->timezone('America/Lima');

Schedule::command('trimax:sync-ordenes-sede')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('trimax:sync-asignacion-bases')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
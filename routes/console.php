<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\AlertaSlaRequerimientosJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new AlertaSlaRequerimientosJob)
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();
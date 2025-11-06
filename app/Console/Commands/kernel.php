<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\ManageIpBlacklist::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Limpiar bloqueos expirados cada hora
        $schedule->command('ip:manage cleanup')->hourly();

        // Limpiar intentos fallidos antiguos cada día
        $schedule->call(function () {
            \App\Models\FailedLoginAttempt::cleanup(30);
        })->daily();

        // Cerrar sesiones inactivas cada 10 minutos
        $schedule->call(function () {
            \App\Models\UserSession::where('is_online', true)
                ->where('last_activity', '<', now()->subMinutes(30))
                ->update([
                    'is_online' => false,
                    'logout_at' => now(),
                ]);
        })->everyTenMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run inventory alerts check daily at 8 AM
        $schedule->command('inventory:check-alerts')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/inventory-alerts.log'));

        // Also run at 6 PM for afternoon alerts
        $schedule->command('inventory:check-alerts')
            ->dailyAt('18:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/inventory-alerts.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

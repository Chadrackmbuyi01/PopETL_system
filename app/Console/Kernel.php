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
         // Run ETL daily at midnight for the default country list.
        // withoutOverlapping() prevents duplicate jobs from stacking.
        $schedule->command('population:fetch')
            ->daily()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/population-etl.log'));
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
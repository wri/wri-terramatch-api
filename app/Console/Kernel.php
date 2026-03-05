<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('remove-verifications')->everyFiveMinutes()->onOneServer();
        $schedule->command('remove-password-resets')->everyFiveMinutes()->onOneServer();
        $schedule->command('remove-export-files')->daily();
        $schedule->command('remove-notifications')->everyFiveMinutes()->onOneServer();
        $schedule->command('generate-application-export')->twiceDaily(13, 20)->onOneServer();
        $schedule->command('generate-admin-all-entity-records-export')->twiceDaily(13, 20)->onOneServer();
        $schedule->command('send-daily-digest-notifications')->weeklyOn(1, '17:00')->timezone('Europe/Sofia')->onOneServer();
        $schedule->command('send-weekly-polygon-update-notifications')->weeklyOn(1, '00:00')->timezone('Europe/Sofia')->onOneServer();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}

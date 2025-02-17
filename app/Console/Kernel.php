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
        /**
         * 2023-08-03 - Stopped the remove uploads script because it's a more reliable
         * fix than attempting to alter the raw SQL statement
         */
        // $schedule->command('remove-uploads')->everyFiveMinutes();
        $schedule->command('remove-export-files')->daily();
        $schedule->command('remove-elevator-videos')->everyFiveMinutes();
        $schedule->command('remove-notifications')->everyFiveMinutes()->onOneServer();
        $schedule->command('remove-notifications-buffers')->everyFiveMinutes()->onOneServer();
        $schedule->command('remove-filter-records')->everyFiveMinutes()->onOneServer();
        $schedule->command('find-matches')->everyMinute()->onOneServer();
        $schedule->command('create-visibility-notifications')->everyFiveMinutes()->onOneServer();
        // This one notifies to Slack, which is not an integration we currently have set up. Leaving it in place with
        // plans to re-instate it soon.
        // $schedule->command('check-queue-length')->everyFiveMinutes()->onOneServer();
        $schedule->command('send-upcoming-progress-update-notifications')->daily()->onOneServer();
        $schedule->command('generate-control-site-due-submissions')->weeklyOn(5, '00:00')->onOneServer();
        $schedule->command('process-scheduled-jobs')->everyFiveMinutes()->onOneServer();
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

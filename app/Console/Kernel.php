<?php

namespace App\Console;

use App\Jobs\V2\CreateTaskDueJob;
use App\Jobs\V2\SendReportRemindersJob;
use App\Jobs\V2\SendSiteAndNurseryRemindersJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('remove-verifications')->everyFiveMinutes();
        $schedule->command('remove-password-resets')->everyFiveMinutes();
        /**
         * 2023-08-03 - Stopped the remove uploads script because it's a more reliable
         * fix than attempting to alter the raw SQL statement
         */
        // $schedule->command('remove-uploads')->everyFiveMinutes();
        $schedule->command('remove-export-files')->daily();
        $schedule->command('remove-elevator-videos')->everyFiveMinutes();
        $schedule->command('remove-notifications')->everyFiveMinutes();
        $schedule->command('remove-notifications-buffers')->everyFiveMinutes();
        $schedule->command('remove-filter-records')->everyFiveMinutes();
        $schedule->command('find-matches')->everyMinute();
        $schedule->command('create-visibility-notifications')->everyFiveMinutes();
        $schedule->command('check-queue-length')->everyFiveMinutes();
        $schedule->command('send-upcoming-progress-update-notifications')->daily();
        $schedule->command('generate-control-site-due-submissions')->weeklyOn(5, '00:00');

        // PPC report jobs
        $schedule->job(new CreateTaskDueJob('ppc', 4, 4))->yearlyOn(3, 14);
        $schedule->job(new CreateTaskDueJob('ppc', 7, 5))->yearlyOn(6, 14);
        $schedule->job(new CreateTaskDueJob('ppc', 10, 4))->yearlyOn(9, 13);
        $schedule->job(new CreateTaskDueJob('ppc', 1, 3))->yearlyOn(12, 13);

        // Terrafund report jobs
        $schedule->job(new CreateTaskDueJob('terrafund'))->yearlyOn(12, 31);
        $schedule->job(new CreateTaskDueJob('terrafund'))->yearlyOn(6, 30);

        $schedule->job(new SendReportRemindersJob('terrafund'))->yearlyOn(5, 30);
        $schedule->job(new SendReportRemindersJob('terrafund'))->yearlyOn(11, 30);

        $schedule->job(new SendSiteAndNurseryRemindersJob('terrafund'))->yearlyOn(5, 30);
        $schedule->job(new SendSiteAndNurseryRemindersJob('terrafund'))->yearlyOn(11, 30);

        $schedule->command('generate-application-export')->twiceDaily(13, 20);
        $schedule->command('generate-admin-all-entity-records-export')->twiceDaily(13, 20);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}

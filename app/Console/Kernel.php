<?php

namespace App\Console;

use App\Jobs\V2\CreateNurseryReportDueJob;
use App\Jobs\V2\CreateProjectReportDueJob;
use App\Jobs\V2\CreateSiteReportDueJob;
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
        $schedule->job(new CreateSiteReportDueJob('ppc', 4))->yearlyOn(1, 7);
        $schedule->job(new CreateProjectReportDueJob('ppc', 4))->yearlyOn(1, 7);

        $schedule->job(new CreateSiteReportDueJob('ppc', 7))->yearlyOn(4, 7);
        $schedule->job(new CreateProjectReportDueJob('ppc', 7))->yearlyOn(4, 7);

        $schedule->job(new CreateSiteReportDueJob('ppc', 10))->yearlyOn(7, 7);
        $schedule->job(new CreateProjectReportDueJob('ppc', 10))->yearlyOn(7, 7);

        $schedule->job(new CreateSiteReportDueJob('ppc', 1))->yearlyOn(10, 6);
        $schedule->job(new CreateProjectReportDueJob('ppc', 1))->yearlyOn(10, 6);

        // Terrafund report jobs
        $schedule->job(new CreateProjectReportDueJob('terrafund'))->yearlyOn(12, 31);
        $schedule->job(new CreateSiteReportDueJob('terrafund'))->yearlyOn(12, 31);
        $schedule->job(new CreateNurseryReportDueJob('terrafund'))->yearlyOn(12, 31);

        $schedule->job(new CreateProjectReportDueJob('terrafund'))->yearlyOn(6, 30);
        $schedule->job(new CreateSiteReportDueJob('terrafund'))->yearlyOn(6, 30);
        $schedule->job(new CreateNurseryReportDueJob('terrafund'))->yearlyOn(6, 30);

        $schedule->job(new SendReportRemindersJob('terrafund'))->yearlyOn(5, 30);
        $schedule->job(new SendReportRemindersJob('terrafund'))->yearlyOn(11, 30);

        $schedule->job(new SendSiteAndNurseryRemindersJob('terrafund'))->yearlyOn(5, 30);
        $schedule->job(new SendSiteAndNurseryRemindersJob('terrafund'))->yearlyOn(11, 30);

        $schedule->command('generate-application-export')->twiceDaily(13, 20);
        $schedule->command('generate-admin-all-entity-records-export')->twiceDaily(13, 20);
        $schedule->command('populate-v2-temporary-sites')->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}

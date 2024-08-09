<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\ScheduledJobs\ReportReminderJob;
use App\Models\V2\ScheduledJobs\SiteAndNurseryReminderJob;
use App\Models\V2\ScheduledJobs\TaskDueJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedScheduledJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:seed-scheduled-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the initial definition of scheduled jobs to replace what was in Kernel.php';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // PPC reports
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 9)->startOfMonth()->setDay(13),
            'ppc',
            Carbon::createFromFormat('m', 10)->startOfMonth()->setDay(4),
        );
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 12)->startOfMonth()->setDay(13),
            'ppc',
            Carbon::createFromFormat('m', 1)->addYear()->startOfMonth()->setDay(3),
        );
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 3)->addYear()->startOfMonth()->setDay(14),
            'ppc',
            Carbon::createFromFormat('m', 4)->addYear()->startOfMonth()->setDay(4),
        );
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 6)->addYear()->startOfMonth()->setDay(14),
            'ppc',
            Carbon::createFromFormat('m', 7)->addYear()->startOfMonth()->setDay(5),
        );

        // Terrafund reports
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 12)->startOfMonth()->setDay(31),
            'terrafund',
            Carbon::createFromFormat('m', 1)->addYear()->startOfMonth()->setDay(31),
        );
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 6)->addYear()->startOfMonth()->setDay(30),
            'terrafund',
            Carbon::createFromFormat('m', 7)->addYear()->startOfMonth()->setDay(30),
        );

        // Terrafund reminders
        ReportReminderJob::createReportReminder(
            Carbon::createFromFormat('m', 11)->startOfMonth()->setDay(30),
            'terrafund'
        );
        ReportReminderJob::createReportReminder(
            Carbon::createFromFormat('m', 5)->addYear()->startOfMonth()->setDay(30),
            'terrafund'
        );
        SiteAndNurseryReminderJob::createSiteAndNurseryReminder(
            Carbon::createFromFormat('m', 11)->startOfMonth()->setDay(30),
            'terrafund'
        );
        SiteAndNurseryReminderJob::createSiteAndNurseryReminder(
            Carbon::createFromFormat('m', 5)->addYear()->startOfMonth()->setDay(30),
            'terrafund'
        );
    }
}

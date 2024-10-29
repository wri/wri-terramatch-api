<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\ScheduledJobs\TaskDueJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedScheduledJobsHbfEnterprises extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:seed-scheduled-jobs-hbf-enterprises';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the initial definition of scheduled jobs for hbf and enterprises frameworks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // HBF reports
        // May-October, November 1, December 1
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 11)->startOfMonth()->setDay(1),
            'hbf',
            Carbon::createFromFormat('m', 12)->startOfMonth()->setDay(1),
        );
        // November-April, May 1, June 1
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 5)->startOfMonth()->setDay(1),
            'hbf',
            Carbon::createFromFormat('m', 6)->startOfMonth()->setDay(3),
        );

        // Enterprises reports
        // January-June, July 1, July 30
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 7)->startOfMonth()->setDay(1),
            'enterprises',
            Carbon::createFromFormat('m', 7)->startOfMonth()->setDay(30),
        );
        // July-December, January 1, January 30
        TaskDueJob::createTaskDue(
            Carbon::createFromFormat('m', 1)->startOfMonth()->setDay(1),
            'enterprises',
            Carbon::createFromFormat('m', 1)->startOfMonth()->setDay(30),
        );

    }
}

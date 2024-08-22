<?php

namespace App\Console\Commands;

use App\Models\V2\ScheduledJobs\ScheduledJob;
use Illuminate\Console\Command;

class ProcessScheduledJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-scheduled-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for jobs ready to be executed and executes them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ScheduledJob::readyToExecute()->each(function ($job) {
            $job->execute();
        });
    }
}

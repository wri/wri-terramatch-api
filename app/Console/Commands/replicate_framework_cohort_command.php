<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class replicate_framework_cohort_command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replicate_framework_cohort_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to copy framework_key to cohort';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projects = Project::all();
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, count($projects));
        $progressBar->setFormat('Processing: %current% [%bar%] %percent:3s%%');
        $progressBar->start();

        foreach ($projects as $project) {
            $framework_key = $project->framework_key;
            $project->cohort = $framework_key;
            $project->save();
            $progressBar->advance();
        }
        $progressBar->finish();
    }
}

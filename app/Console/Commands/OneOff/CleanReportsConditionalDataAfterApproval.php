<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Console\Command;

class CleanReportsConditionalDataAfterApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:clean-reports-conditional-data-after-approval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean reports conditional data after approval';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning data on Project Reports...');
        $this->cleanReports(ProjectReport::where('status', EntityStatusStateMachine::APPROVED));
        $this->info("\n\nCleaning data on Site Reports...");
        $this->cleanReports(SiteReport::where('status', EntityStatusStateMachine::APPROVED));
        $this->info("\n\nCleaning data on Nursery Reports...");
        $this->cleanReports(NurseryReport::where('status', EntityStatusStateMachine::APPROVED));
        $this->info("\n\nDone!");
    }

    private function cleanReports($query)
    {
        $count = (clone $query)->count();
        $this->withProgressBar($count, function ($progressBar) use ($query) {
            $query->chunk(100, function ($chunk) use (&$progressBar) {
                foreach ($chunk as $report) {
                    $report->cleanUpConditionalData();
                    $progressBar->advance();
                }
            });
        });
    }
}

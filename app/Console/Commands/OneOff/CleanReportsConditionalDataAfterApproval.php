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
        ProjectReport::where('status', EntityStatusStateMachine::APPROVED)->chunk(100, function ($chunk) {
            foreach ($chunk as $projectReport) {
                $projectReport->cleanUpConditionalData();
            }
        });
        SiteReport::where('status', EntityStatusStateMachine::APPROVED)->chunk(100, function ($chunk) {
            foreach ($chunk as $siteReport) {
                $siteReport->cleanUpConditionalData();
            }
        });
        NurseryReport::where('status', EntityStatusStateMachine::APPROVED)->chunk(100, function ($chunk) {
            foreach ($chunk as $nurseryReport) {
                $nurseryReport->cleanUpConditionalData();
            }
        });
    }
}

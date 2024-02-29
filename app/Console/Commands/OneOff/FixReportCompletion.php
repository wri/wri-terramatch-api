<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\ReportModel;
use App\Models\V2\Sites\SiteReport;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Console\Command;

class FixReportCompletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:fix-report-completion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes reports that have an incorrect completion due to being updated before the computation code was fixed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalUpdated = 0;
        $totalAlreadyCorrect = 0;
        collect([ProjectReport::class, SiteReport::class, NurseryReport::class])->each(
            function($modelClass) use (&$totalUpdated, &$totalAlreadyCorrect) {
                $modelClass::whereNot('status', ReportStatusStateMachine::DUE)->where('completion', '<', 100)->chunkById(
                    100,
                    function ($reports) use (&$totalUpdated, &$totalAlreadyCorrect) {
                        /** @var ReportModel $report */
                        foreach ($reports as $report) {
                            $initialValue = $report->completion;
                            $report->calculateCompletion($report->getForm());
                            if ($initialValue != $report->completion) {
                                $report->save();
                                $totalUpdated++;
                            } else {
                                $totalAlreadyCorrect++;
                            }
                        }
                    }
                );
            }
        );

        $this->info("Total reports updated: $totalUpdated. $totalAlreadyCorrect reports already had the correct value.");
    }
}

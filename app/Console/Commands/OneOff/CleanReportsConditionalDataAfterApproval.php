<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function handle(): int
    {
        try {
            $this->info('Cleaning data on Project Reports...');
            $this->cleanReports(ProjectReport::where('status', EntityStatusStateMachine::APPROVED), 'ProjectReport');

            $this->info("\n\nCleaning data on Site Reports...");
            $this->cleanReports(SiteReport::where('status', EntityStatusStateMachine::APPROVED), 'SiteReport');

            $this->info("\n\nCleaning data on Nursery Reports...");
            $this->cleanReports(NurseryReport::where('status', EntityStatusStateMachine::APPROVED), 'NurseryReport');

            $this->info("\n\nDone!");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred while cleaning reports: ' . $e->getMessage());
            Log::error('CleanReportsConditionalDataAfterApproval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Clean conditional data for reports matching the query.
     *
     * @param  Builder<Model>  $query
     * @param  string  $reportType
     */
    private function cleanReports(Builder $query, string $reportType): void
    {
        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info("No {$reportType} reports found with approved status.");

            return;
        }

        $processedCount = 0;
        $errorCount = 0;

        $this->withProgressBar($count, function ($progressBar) use ($query, $reportType, &$processedCount, &$errorCount) {
            $query->chunk(100, function ($chunk) use (&$progressBar, $reportType, &$processedCount, &$errorCount) {
                foreach ($chunk as $report) {
                    try {
                        DB::transaction(function () use ($report) {
                            $report->cleanUpConditionalData();
                        });
                        $processedCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        Log::warning("Failed to clean conditional data for {$reportType}", [
                            'report_uuid' => $report->uuid ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $progressBar->advance();
                }
            });
        });

        $this->newLine();
        $this->info("{$reportType}: Processed {$processedCount}, Errors: {$errorCount}");
    }
}

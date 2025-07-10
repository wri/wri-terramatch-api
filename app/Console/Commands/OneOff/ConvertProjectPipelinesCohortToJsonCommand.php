<?php

namespace App\Console\Commands\OneOff;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertProjectPipelinesCohortToJsonCommand extends Command
{
    protected $signature = 'one-off:convert-project-pipelines-cohort-to-json 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Convert project_pipelines.cohort from string to JSON array format';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Converting project_pipelines.cohort from string to JSON array format...');

        $pipelines = DB::table('project_pipelines')
            ->whereNotNull('cohort')
            ->where('cohort', '!=', '')
            ->whereRaw('JSON_VALID(cohort) = 0')
            ->get(['id', 'cohort', 'name']);

        if ($pipelines->isEmpty()) {
            $this->info('No project pipelines found with string cohort values. All cohorts are already in JSON format.');

            return 0;
        }

        $this->info("Found {$pipelines->count()} project pipelines with string cohort values:");

        $cohortGroups = $pipelines->groupBy('cohort');
        foreach ($cohortGroups as $cohortValue => $pipelinesGroup) {
            $this->line("   - '{$cohortValue}': {$pipelinesGroup->count()} pipelines");
        }

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made. Here are the conversions that would happen:');
            foreach ($pipelines as $pipeline) {
                $newValue = json_encode([$pipeline->cohort]);
                $this->line("   Pipeline ID {$pipeline->id} ('{$pipeline->name}'): '{$pipeline->cohort}' → {$newValue}");
            }

            return 0;
        }

        if (! $force && ! $this->confirm('Do you want to proceed with the conversion?')) {
            $this->info('Operation cancelled.');

            return 1;
        }

        $this->info('Starting conversion...');

        $successCount = 0;
        $errorCount = 0;

        DB::transaction(function () use ($pipelines, &$successCount, &$errorCount) {
            foreach ($pipelines as $pipeline) {
                try {
                    $cohortArray = [$pipeline->cohort];

                    DB::table('project_pipelines')
                        ->where('id', $pipeline->id)
                        ->update(['cohort' => json_encode($cohortArray)]);

                    $successCount++;
                    $this->line("   ✅ Pipeline ID {$pipeline->id}: '{$pipeline->cohort}' → " . json_encode($cohortArray));
                } catch (Exception $e) {
                    $errorCount++;
                    $this->error("   ❌ Failed to convert Pipeline ID {$pipeline->id}: {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        if ($successCount > 0) {
            $this->info("Successfully converted {$successCount} project pipelines.");
        }
        if ($errorCount > 0) {
            $this->error("Failed to convert {$errorCount} project pipelines.");
        }

        $this->info('Conversion completed!');

        return $errorCount > 0 ? 1 : 0;
    }
}

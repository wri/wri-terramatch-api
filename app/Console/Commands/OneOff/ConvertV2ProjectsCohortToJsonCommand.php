<?php

namespace App\Console\Commands\OneOff;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertV2ProjectsCohortToJsonCommand extends Command
{
    protected $signature = 'one-off:convert-v2-projects-cohort-to-json 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Convert v2_projects.cohort from string to JSON array format';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Converting v2_projects.cohort from string to JSON array format...');

        $projects = DB::table('v2_projects')
            ->whereNotNull('cohort')
            ->where('cohort', '!=', '')
            ->whereRaw('JSON_VALID(cohort) = 0')
            ->get(['id', 'cohort', 'name']);

        if ($projects->isEmpty()) {
            $this->info('No projects found with string cohort values. All cohorts are already in JSON format.');

            return 0;
        }

        $this->info("Found {$projects->count()} projects with string cohort values:");

        $cohortGroups = $projects->groupBy('cohort');
        foreach ($cohortGroups as $cohortValue => $projectsGroup) {
            $this->line("   - '{$cohortValue}': {$projectsGroup->count()} projects");
        }

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made. Here are the conversions that would happen:');
            foreach ($projects as $project) {
                $newValue = json_encode([$project->cohort]);
                $this->line("   Project ID {$project->id} ('{$project->name}'): '{$project->cohort}' → {$newValue}");
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

        DB::transaction(function () use ($projects, &$successCount, &$errorCount) {
            foreach ($projects as $project) {
                try {
                    $cohortArray = [$project->cohort];

                    DB::table('v2_projects')
                        ->where('id', $project->id)
                        ->update(['cohort' => json_encode($cohortArray)]);

                    $successCount++;
                    $this->line("   ✅ Project ID {$project->id}: '{$project->cohort}' → " . json_encode($cohortArray));
                } catch (Exception $e) {
                    $errorCount++;
                    $this->error("   ❌ Failed to convert Project ID {$project->id}: {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        if ($successCount > 0) {
            $this->info("Successfully converted {$successCount} projects.");
        }
        if ($errorCount > 0) {
            $this->error("Failed to convert {$errorCount} projects.");
        }

        $this->info('Conversion completed!');

        return $errorCount > 0 ? 1 : 0;
    }
}

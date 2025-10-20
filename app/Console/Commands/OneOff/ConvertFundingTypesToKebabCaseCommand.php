<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertFundingTypesToKebabCaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:convert-funding-types-to-kebab-case {--dry-run : Show what would be changed without making actual changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert v2_funding_types.type field from snake_case to kebab-case format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting conversion of v2_funding_types.type from snake_case to kebab-case...');

        // Get all distinct type values that contain underscores
        $typesWithUnderscores = DB::table('v2_funding_types')
            ->select('type')
            ->distinct()
            ->where('type', 'LIKE', '%_%')
            ->get();

        if ($typesWithUnderscores->isEmpty()) {
            $this->info('No records found with snake_case type values. Nothing to convert.');

            return 0;
        }

        $this->info('Found ' . $typesWithUnderscores->count() . ' distinct type values that need conversion:');

        $conversionMap = [];
        foreach ($typesWithUnderscores as $type) {
            $originalType = $type->type;
            $kebabCaseType = str_replace('_', '-', $originalType);
            $conversionMap[$originalType] = $kebabCaseType;

            $this->line("  '{$originalType}' -> '{$kebabCaseType}'");
        }

        // Also check for any additional funding types from config that might exist
        $configFundingTypes = config('wri.funding-types', []);
        foreach ($configFundingTypes as $key => $label) {
            if (strpos($key, '_') !== false) {
                $kebabCaseKey = str_replace('_', '-', $key);
                if (! isset($conversionMap[$key])) {
                    $conversionMap[$key] = $kebabCaseKey;
                    $this->line("  '{$key}' -> '{$kebabCaseKey}' (from config)");
                }
            }
        }

        if ($isDryRun) {
            $this->info('DRY RUN: Would convert ' . count($conversionMap) . ' type values');

            return 0;
        }

        // Confirm before proceeding
        if (! $this->confirm('Do you want to proceed with the conversion?')) {
            $this->info('Conversion cancelled.');

            return 0;
        }

        $totalUpdated = 0;
        $errors = [];

        // Process each conversion
        foreach ($conversionMap as $originalType => $kebabCaseType) {
            try {
                $updated = DB::table('v2_funding_types')
                    ->where('type', $originalType)
                    ->update(['type' => $kebabCaseType]);

                $totalUpdated += $updated;
                $this->info("Updated {$updated} records: '{$originalType}' -> '{$kebabCaseType}'");
            } catch (\Exception $e) {
                $errors[] = "Failed to convert '{$originalType}': " . $e->getMessage();
                $this->error("Failed to convert '{$originalType}': " . $e->getMessage());
            }
        }

        $this->info('Conversion completed!');
        $this->info("Total records updated: {$totalUpdated}");

        if (! empty($errors)) {
            $this->error('Errors encountered:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }

            return 1;
        }

        // Verify the conversion
        $this->info('Verifying conversion...');
        $remainingSnakeCase = DB::table('v2_funding_types')
            ->where('type', 'LIKE', '%_%')
            ->count();

        if ($remainingSnakeCase > 0) {
            $this->warn("Warning: {$remainingSnakeCase} records still contain underscores in type field");

            // Show what values still need conversion
            $remainingTypes = DB::table('v2_funding_types')
                ->select('type')
                ->distinct()
                ->where('type', 'LIKE', '%_%')
                ->get();

            $this->info('Remaining snake_case values:');
            foreach ($remainingTypes as $type) {
                $this->line("  - '{$type->type}'");
            }

            $this->info('You may need to run this command again to catch all values.');
        } else {
            $this->info('✓ All type values successfully converted to kebab-case');
        }

        return 0;
    }
}

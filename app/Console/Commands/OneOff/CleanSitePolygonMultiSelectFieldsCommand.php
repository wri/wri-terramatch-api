<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanSitePolygonMultiSelectFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:clean-site-polygon-multi-select-fields {--dry-run : Run without making any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean and standardize distr and practice fields in site_polygon table to consistent multi-select format (comma-separated, sorted, no spaces)';

    /**
     * Individual value mappings for practice (for normalizing single values within comma-separated strings)
     *
     * @var array
     */
    protected $practiceValueMapping = [
        'assisted natural regeneration' => 'assisted-natural-regeneration',
        'assisted-natural regeneration' => 'assisted-natural-regeneration',
        'assisted-natural-regeneration-(anr)' => 'assisted-natural-regeneration',
        'assisted-naturalregeneration' => 'assisted-natural-regeneration',
        'asisted-natural-regeneration' => 'assisted-natural-regeneration',
        'anr' => 'assisted-natural-regeneration',
        'agroforestry' => 'assisted-natural-regeneration',
        'agroforestry-systems' => 'direct-seeding',
        'plantations' => 'assisted-natural-regeneration',
        'silvopasture' => 'assisted-natural-regeneration',
        'direct-seedling' => 'direct-seeding',
        'seed-dispersal' => 'direct-seeding',
        'tree planting' => 'tree-planting',
        'tree planting/rna' => 'tree-planting',
        'reforestation' => 'tree-planting',
        'control' => null,
        'na (control)' => null,
        'na-(control)' => null,
        'n/a' => null,
        'null' => null,
        '' => null,
    ];

    /**
     * Individual value mappings for distr (for normalizing single values within comma-separated strings)
     *
     * @var array
     */
    protected $distrValueMapping = [
        'along-edges' => 'single-line',
        'single line' => 'single-line',
        'whole' => 'full',
        'full-enrichment' => 'full',
        'full coverage' => 'full',
        'partial coverage_perimetral' => 'partial',
        'partial,planting-in-patches' => 'partial',
        '33-11-unknowndist' => null,
        '33-13-unknowndist' => null,
        '33-15-unknowndist' => null,
        '33-16-unknowndist' => null,
        '33-17-unknowndist' => null,
        '33-10-unknowndist' => null,
        '33-14-unknowndist' => null,
        'null' => null,
        'n/a' => null,
        '' => null,
    ];

    /**
     * Valid values for practice field
     *
     * @var array
     */
    protected $validPractices = ['assisted-natural-regeneration', 'direct-seeding', 'tree-planting'];

    /**
     * Valid values for distr field
     *
     * @var array
     */
    protected $validDistr = ['full', 'partial', 'single-line'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting cleaning of site_polygon distr and practice fields...');
        $this->newLine();

        if (! $isDryRun) {
            DB::beginTransaction();
        }

        try {
            $distrChanges = $this->cleanDistrColumn($isDryRun);
            $this->newLine();
            $practiceChanges = $this->cleanPracticeColumn($isDryRun);

            $totalChanges = $distrChanges + $practiceChanges;

            if (! $isDryRun) {
                DB::commit();
                $this->newLine();
                $this->info("✓ Cleaning completed successfully! Total records updated: {$totalChanges}");
            } else {
                $this->newLine();
                $this->info("✓ Dry run completed. {$totalChanges} records would be updated if run without --dry-run option.");
            }

            return 0;
        } catch (\Exception $e) {
            if (! $isDryRun) {
                DB::rollBack();
            }

            $this->error('An error occurred during cleaning: ' . $e->getMessage());
            Log::error('Site polygon multi-select cleaning error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return 1;
        }
    }

    /**
     * Normalize a comma-separated string by:
     * 1. Splitting by comma
     * 2. Trimming and normalizing each individual value
     * 3. Filtering to only valid values
     * 4. Sorting alphabetically
     * 5. Joining with commas (no spaces)
     *
     * @param string|null $value
     * @param array $valueMapping Mapping of individual values to normalized values
     * @param array $validValues Array of valid final values
     * @return string|null
     */
    protected function normalizeCommaSeparatedValue(?string $value, array $valueMapping, array $validValues): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Split by comma and trim each value
        $values = explode(',', $value);
        $values = array_map('trim', $values);
        $values = array_filter($values, function($v) {
            return $v !== '';
        });

        if (empty($values)) {
            return null;
        }

        // Normalize each individual value
        $normalizedValues = [];
        foreach ($values as $val) {
            $lowerVal = strtolower($val);
            
            // Check value mapping first (case-insensitive)
            $mapped = null;
            foreach ($valueMapping as $key => $mappedValue) {
                if (strtolower($key) === $lowerVal) {
                    $mapped = $mappedValue;
                    break;
                }
            }

            // If mapped to null, skip it
            if ($mapped === null && !in_array($val, $validValues)) {
                // Try to find a partial match or exact match with different case
                if (in_array(strtolower($val), array_map('strtolower', $validValues))) {
                    // Find the correct case
                    foreach ($validValues as $validVal) {
                        if (strtolower($validVal) === strtolower($val)) {
                            $mapped = $validVal;
                            break;
                        }
                    }
                }
            }

            // Use mapped value or original if already valid
            $finalValue = $mapped !== null ? $mapped : (in_array($val, $validValues) ? $val : null);

            // Only add if it's a valid value
            if ($finalValue !== null && in_array($finalValue, $validValues)) {
                $normalizedValues[] = $finalValue;
            }
        }

        // Remove duplicates
        $normalizedValues = array_unique($normalizedValues);

        if (empty($normalizedValues)) {
            return null;
        }

        // Sort alphabetically
        sort($normalizedValues);

        // Join with commas (no spaces)
        return implode(',', $normalizedValues);
    }

    /**
     * Clean the distr column
     *
     * @param bool $isDryRun
     * @return int
     */
    protected function cleanDistrColumn($isDryRun = false)
    {
        $this->info('📊 Cleaning distr column...');

        // Get all unique distr values from the database
        $uniqueValues = SitePolygon::withTrashed()
            ->whereNotNull('distr')
            ->where('distr', '!=', '')
            ->distinct()
            ->pluck('distr')
            ->toArray();

        if (empty($uniqueValues)) {
            $this->warn('  No distr values found to process.');
            return 0;
        }

        // Process each unique value
        $valuesToUpdate = [];
        foreach ($uniqueValues as $originalValue) {
            $normalizedValue = $this->normalizeCommaSeparatedValue(
                $originalValue,
                $this->distrValueMapping,
                $this->validDistr
            );

            // Only update if the value changed
            if ($normalizedValue !== $originalValue) {
                $count = SitePolygon::withTrashed()->where('distr', $originalValue)->count();
                if ($count > 0) {
                    $valuesToUpdate[$originalValue] = [
                        'new_value' => $normalizedValue,
                        'count' => $count,
                    ];
                }
            }
        }

        $totalChanges = array_sum(array_column($valuesToUpdate, 'count'));

        if (empty($valuesToUpdate)) {
            $this->info('  ✓ All distr values are already in standard format.');
            return 0;
        }

        $this->info("  Found " . count($valuesToUpdate) . " different distr values that need cleaning:");
        foreach ($valuesToUpdate as $oldValue => $info) {
            $this->line("    - '{$oldValue}' → " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'") .
                         " ({$info['count']} records)");
        }

        if ($isDryRun) {
            return $totalChanges;
        }

        $this->info('  Updating distr values...');
        foreach ($valuesToUpdate as $oldValue => $info) {
            SitePolygon::withTrashed()->where('distr', $oldValue)
                ->update(['distr' => $info['new_value']]);
        }

        $this->info("  ✓ Updated {$totalChanges} records in distr column.");

        return $totalChanges;
    }

    /**
     * Clean the practice column
     *
     * @param bool $isDryRun
     * @return int
     */
    protected function cleanPracticeColumn($isDryRun = false)
    {
        $this->info('🌱 Cleaning practice column...');

        // Get all unique practice values from the database
        $uniqueValues = SitePolygon::withTrashed()
            ->whereNotNull('practice')
            ->where('practice', '!=', '')
            ->distinct()
            ->pluck('practice')
            ->toArray();

        if (empty($uniqueValues)) {
            $this->warn('  No practice values found to process.');
            return 0;
        }

        // Process each unique value
        $valuesToUpdate = [];
        foreach ($uniqueValues as $originalValue) {
            $normalizedValue = $this->normalizeCommaSeparatedValue(
                $originalValue,
                $this->practiceValueMapping,
                $this->validPractices
            );

            // Only update if the value changed
            if ($normalizedValue !== $originalValue) {
                $count = SitePolygon::withTrashed()->where('practice', $originalValue)->count();
                if ($count > 0) {
                    $valuesToUpdate[$originalValue] = [
                        'new_value' => $normalizedValue,
                        'count' => $count,
                    ];
                }
            }
        }

        $totalChanges = array_sum(array_column($valuesToUpdate, 'count'));

        if (empty($valuesToUpdate)) {
            $this->info('  ✓ All practice values are already in standard format.');
            return 0;
        }

        $this->info("  Found " . count($valuesToUpdate) . " different practice values that need cleaning:");
        foreach ($valuesToUpdate as $oldValue => $info) {
            $this->line("    - '{$oldValue}' → " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'") .
                         " ({$info['count']} records)");
        }

        if ($isDryRun) {
            return $totalChanges;
        }

        $this->info('  Updating practice values...');
        foreach ($valuesToUpdate as $oldValue => $info) {
            SitePolygon::withTrashed()->where('practice', $oldValue)
                ->update(['practice' => $info['new_value']]);
        }

        $this->info("  ✓ Updated {$totalChanges} records in practice column.");

        return $totalChanges;
    }
}

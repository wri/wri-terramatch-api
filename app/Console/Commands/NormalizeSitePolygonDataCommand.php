<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NormalizeSitePolygonDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site-polygon:normalize-data {--dry-run : Run without making any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize the distr, target_sys, and practice columns in the site_polygon table';

    // Mapping arrays for data normalization
    protected $distrMapping = [
        'partial,full' => 'full,partial',
        'along-edges' => 'single-line',
        'single-line, full' => 'full,single-line',
        'full, partial, single-line' => 'full,partial,single-line',
        'single line' => 'single-line',
        'partial, full' => 'full,partial',
        'whole' => 'full',
        'full-enrichment' => 'full',
        'Full coverage' => 'full',
        'Full area plantation, Partial plantation, Line plantation' => 'full,partial,single-line',
        'single-line, partial' => 'partial,single-line',
        'partial, single-line' => 'partial,single-line',
        'Full area plantation, Line plantation' => 'full,single-line',
        '33-11-unknowndist' => null,
        '33-13-unknowndist' => null,
        '33-15-unknowndist' => null,
        '33-16-unknowndist' => null,
        '33-17-unknowndist' => null,
        '33-10-unknowndist' => null,
        '33-14-unknowndist' => null,
        'single-line,partial' => 'partial,single-line',
        'single-line,full' => 'full,single-line',
        'full, single-line' => 'full,single-line',
        'Null' => null,
        'partial coverage_perimetral' => 'partial',
        'Partial plantation, Line plantation' => 'partial,single-line',
        'N/A' => null,
        'partial,planting-in-patches' => 'partial',
        '' => null,
    ];

    protected $targetSysMapping = [
        'riparian-area-or-wetland, woodlot-or-plantation' => 'riparian-area-or-wetland',
        'riparian-area-or-wetland,woodlot-or-plantation' => 'riparian-area-or-wetland',
        'Natural Forest' => 'natural-forest',
        'agroforesty' => 'agroforest',
        'Open natural ecosystem or Grasslands' => 'natural-forest',
        'Agroforestry' => 'agroforest',
        'Tree Planting' => 'natural-forest',
        'N/A' => null,
        'Null' => null,
        '' => null,
        'open-natural-ecosystem' => 'natural-forest',
    ];

    protected $practiceMapping = [
        'Assisted Natural Regeneration' => 'assisted-natural-regeneration',
        'NA (control)' => null,
        'agroforestry' => 'assisted-natural-regeneration',
        'agroforestry,planting,enrchment,applied-nucleation,direct-seeding' => 'direct-seeding,tree-planting',
        'agroforestry-systems' => 'direct-seeding',
        'agroforestry-systems,reforestation' => 'direct-seeding',
        'anr' => 'assisted-natural-regeneration',
        'asisted-natural-regeneration, tree-planting' => 'assisted-natural-regeneration,tree-planting',
        'assisted-natural-regeneration, direct-seeding, tree-planting' => 'assisted-natural-regeneration,direct-seeding,tree-planting',
        'assisted-natural regeneration' => 'assisted-natural-regeneration',
        'assisted-natural-regeneration, tree-planting' => 'assisted-natural-regeneration,tree-planting',
        'assisted-natural-regeneration,tree-planting,direct-seeding' => 'assisted-natural-regeneration,direct-seeding,tree-planting',
        'assisted-natural-regeneration-(anr)' => 'assisted-natural-regeneration',
        'assisted-naturalregeneration' => 'assisted-natural-regeneration',
        'control' => null,
        'direct-seeding, assisted-natural-regeneration' => 'assisted-natural-regeneration,direct-seeding',
        'direct-seeding, assisted-natural-regeneration, tree-planting' => 'assisted-natural-regeneration,direct-seeding,tree-planting',
        'direct-seeding, tree-planting' => 'direct-seeding,tree-planting',
        'direct-seeding, tree-planting, assisted-natural-regeneration' => 'assisted-natural-regeneration,direct-seeding,tree-planting',
        'direct-seeding,assisted-natural-regeneration' => 'assisted-natural-regeneration,direct-seeding',
        'direct-seedling' => 'direct-seeding',
        'na-(control)' => null,
        'plantations' => 'assisted-natural-regeneration',
        'reforestation' => 'tree-planting',
        'seed-dispersal' => 'direct-seeding',
        'silvopasture' => 'assisted-natural-regeneration',
        'tree planting' => 'tree-planting',
        'tree-planting, assisted-natural-regeneration' => 'assisted-natural-regeneration,tree-planting',
        'tree-planting, direct-seeding' => 'direct-seeding,tree-planting',
        'tree-planting, direct-seeding, assisted-natural-regeneration' => 'assisted-natural-regeneration,direct-seeding,tree-planting',
        'tree-planting,-anr' => 'assisted-natural-regeneration,tree-planting',
        'tree-planting,assisted-natural-regeneration' => 'assisted-natural-regeneration,tree-planting',
        'tree-planting,direct-seeding' => 'direct-seeding,tree-planting',
        'tree-planting,direct-seeding,applied-nucleation' => 'direct-seeding,tree-planting',
        'tree-planting,direct-seeding,applied-nucleation,cutting' => 'direct-seeding,tree-planting',
        'N/A' => null,
        'Null' => null,
        '' => null,
        'agroforestry-/tree-planting' => 'tree-planting',
        'enrichment-planting' => null,
        'enrichment-planting,assisted-natural-regeneration' => 'assisted-natural-regeneration',
        'tree planting/RNA' => 'tree-planting',
        'tree-planting, direct-seeding, cutting' => 'direct-seeding,tree-planting',
        'tree-planting, assisted-natural-regeneration, direct-seeding' => 'assisted-natural-regeneration,direct-seeding,tree-planting',
    ];

    // Individual value mappings for practice (for normalizing single values within comma-separated strings)
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

    // Individual value mappings for distr (for normalizing single values within comma-separated strings)
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

    // Valid values for each field
    protected $validPractices = ['assisted-natural-regeneration', 'direct-seeding', 'tree-planting'];
    protected $validDistr = ['full', 'partial', 'single-line'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made to the database');
        }

        $this->info('Starting data normalization of site_polygon table...');

        if (! $isDryRun) {
            DB::beginTransaction();
        }

        try {
            $distrChanges = $this->normalizeDistrColumn($isDryRun);
            $targetSysChanges = $this->normalizeTargetSysColumn($isDryRun);
            $practiceChanges = $this->normalizePracticeColumn($isDryRun);

            $totalChanges = $distrChanges + $targetSysChanges + $practiceChanges;

            if (! $isDryRun) {
                DB::commit();
                $this->info("Data normalization completed successfully! Total records updated: {$totalChanges}");
            } else {
                $this->info("Dry run completed. {$totalChanges} records would be updated if run without --dry-run option.");
            }

            return 0;
        } catch (\Exception $e) {
            if (! $isDryRun) {
                DB::rollBack();
            }

            $this->error('An error occurred during normalization: ' . $e->getMessage());
            Log::error('Normalization error: ' . $e->getMessage());

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

    protected function normalizeDistrColumn($isDryRun = false)
    {
        $this->info('Analyzing distr column...');
        
        // First, handle exact matches from the old mapping (for backwards compatibility)
        $exactMatches = [];
        
        foreach ($this->distrMapping as $oldValue => $newValue) {
            if ($oldValue === $newValue) {
                continue;
            }

            $count = SitePolygon::withTrashed()->where('distr', $oldValue)->count();
            if ($count > 0) {
                $exactMatches[$oldValue] = [
                    'new_value' => $newValue,
                    'count' => $count,
                ];
            }
        }

        // Get all unique distr values from the database
        $uniqueValues = SitePolygon::withTrashed()
            ->whereNotNull('distr')
            ->where('distr', '!=', '')
            ->distinct()
            ->pluck('distr')
            ->toArray();

        // Process each unique value
        $valuesToUpdate = [];
        foreach ($uniqueValues as $originalValue) {
            // Skip if already handled by exact match
            if (isset($exactMatches[$originalValue])) {
                continue;
            }

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

        // Combine exact matches with dynamic updates
        $allUpdates = array_merge($exactMatches, $valuesToUpdate);
        $totalChanges = array_sum(array_column($allUpdates, 'count'));

        if (empty($allUpdates)) {
            $this->info('No incorrect distr values found that need updating.');
            return 0;
        }

        $this->info('Found ' . count($allUpdates) . ' different distr values that need correction:');
        foreach ($allUpdates as $oldValue => $info) {
            $this->info("- '{$oldValue}' to " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'") .
                         " ({$info['count']} records)");
        }

        if ($isDryRun) {
            return $totalChanges;
        }

        $this->info('Updating distr values...');
        foreach ($allUpdates as $oldValue => $info) {
            SitePolygon::withTrashed()->where('distr', $oldValue)
                ->update(['distr' => $info['new_value']]);

            $this->info("Updated {$info['count']} records from '{$oldValue}' to " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'"));
        }

        return $totalChanges;
    }

    protected function normalizeTargetSysColumn($isDryRun = false)
    {
        $this->info('Analyzing target_sys column...');
        $totalChanges = 0;

        $valuesToUpdate = [];
        foreach ($this->targetSysMapping as $oldValue => $newValue) {
            if ($oldValue === $newValue) {
                continue;
            }

            $count = SitePolygon::withTrashed()->where('target_sys', $oldValue)->count();
            if ($count > 0) {
                $valuesToUpdate[$oldValue] = [
                    'new_value' => $newValue,
                    'count' => $count,
                ];
                $totalChanges += $count;
            }
        }

        if (empty($valuesToUpdate)) {
            $this->info('No incorrect target_sys values found that need updating.');

            return 0;
        }

        $this->info('Found ' . count($valuesToUpdate) . ' different target_sys values that need correction:');
        foreach ($valuesToUpdate as $oldValue => $info) {
            $this->info("- '{$oldValue}' to '{$info['new_value']}' ({$info['count']} records)");
        }

        if ($isDryRun) {
            return $totalChanges;
        }

        $this->info('Updating target_sys values...');
        foreach ($valuesToUpdate as $oldValue => $info) {
            SitePolygon::withTrashed()->where('target_sys', $oldValue)
                ->update(['target_sys' => $info['new_value']]);

            $this->info("Updated {$info['count']} records from '{$oldValue}' to '{$info['new_value']}'");
        }

        return $totalChanges;
    }

    protected function normalizePracticeColumn($isDryRun = false)
    {
        $this->info('Analyzing practice column...');
        
        // First, handle exact matches from the old mapping (for backwards compatibility)
        $exactMatches = [];
        
        foreach ($this->practiceMapping as $oldValue => $newValue) {
            if ($oldValue === $newValue) {
                continue;
            }

            $count = SitePolygon::withTrashed()->where('practice', $oldValue)->count();
            if ($count > 0) {
                $exactMatches[$oldValue] = [
                    'new_value' => $newValue,
                    'count' => $count,
                ];
            }
        }

        // Get all unique practice values from the database
        $uniqueValues = SitePolygon::withTrashed()
            ->whereNotNull('practice')
            ->where('practice', '!=', '')
            ->distinct()
            ->pluck('practice')
            ->toArray();

        // Process each unique value
        $valuesToUpdate = [];
        foreach ($uniqueValues as $originalValue) {
            // Skip if already handled by exact match
            if (isset($exactMatches[$originalValue])) {
                continue;
            }

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

        // Combine exact matches with dynamic updates
        $allUpdates = array_merge($exactMatches, $valuesToUpdate);
        $totalChanges = array_sum(array_column($allUpdates, 'count'));

        if (empty($allUpdates)) {
            $this->info('No incorrect practice values found that need updating.');
            return 0;
        }

        $this->info('Found ' . count($allUpdates) . ' different practice values that need correction:');
        foreach ($allUpdates as $oldValue => $info) {
            $this->info("- '{$oldValue}' to " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'") .
                         " ({$info['count']} records)");
        }

        if ($isDryRun) {
            return $totalChanges;
        }

        $this->info('Updating practice values...');
        foreach ($allUpdates as $oldValue => $info) {
            SitePolygon::withTrashed()->where('practice', $oldValue)
                ->update(['practice' => $info['new_value']]);

            $this->info("Updated {$info['count']} records from '{$oldValue}' to " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'"));
        }

        return $totalChanges;
    }
}

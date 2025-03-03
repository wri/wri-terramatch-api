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
    ];

    protected $targetSysMapping = [
        'riparian-area-or-wetland, woodlot-or-plantation' => 'riparian-area-or-wetland',
        'riparian-area-or-wetland,woodlot-or-plantation' => 'riparian-area-or-wetland',
        'Natural Forest' => 'natural-forest',
        'agroforesty' => 'agroforest',
        'Open natural ecosystem or Grasslands' => 'natural-forest',
        'Agroforestry' => 'agroforest',
        'Tree Planting' => 'natural-forest',
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
    ];

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

    protected function normalizeDistrColumn($isDryRun = false)
    {
        $this->info('Analyzing distr column...');
        $totalChanges = 0;

        $valuesToUpdate = [];
        foreach ($this->distrMapping as $oldValue => $newValue) {
            if ($oldValue === $newValue) {
                continue;
            }

            $count = SitePolygon::where('distr', $oldValue)->count();
            if ($count > 0) {
                $valuesToUpdate[$oldValue] = [
                    'new_value' => $newValue,
                    'count' => $count,
                ];
                $totalChanges += $count;
            }
        }

        if (empty($valuesToUpdate)) {
            $this->info('No incorrect distr values found that need updating.');

            return 0;
        }

        $this->info('Found ' . count($valuesToUpdate) . ' different distr values that need correction:');
        foreach ($valuesToUpdate as $oldValue => $info) {
            $this->info("- '{$oldValue}' to " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'") .
                         " ({$info['count']} records)");
        }

        if ($isDryRun) {
            return $totalChanges;
        }

        $this->info('Updating distr values...');
        foreach ($valuesToUpdate as $oldValue => $info) {
            SitePolygon::where('distr', $oldValue)
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

            $count = SitePolygon::where('target_sys', $oldValue)->count();
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
            SitePolygon::where('target_sys', $oldValue)
                ->update(['target_sys' => $info['new_value']]);

            $this->info("Updated {$info['count']} records from '{$oldValue}' to '{$info['new_value']}'");
        }

        return $totalChanges;
    }

    protected function normalizePracticeColumn($isDryRun = false)
    {
        $this->info('Analyzing practice column...');
        $totalChanges = 0;

        $valuesToUpdate = [];
        foreach ($this->practiceMapping as $oldValue => $newValue) {
            if ($oldValue === $newValue) {
                continue;
            }

            $count = SitePolygon::where('practice', $oldValue)->count();
            if ($count > 0) {
                $valuesToUpdate[$oldValue] = [
                    'new_value' => $newValue,
                    'count' => $count,
                ];
                $totalChanges += $count;
            }
        }

        if (empty($valuesToUpdate)) {
            $this->info('No incorrect practice values found that need updating.');

            return 0;
        }

        $this->info('Found ' . count($valuesToUpdate) . ' different practice values that need correction:');
        foreach ($valuesToUpdate as $oldValue => $info) {
            $this->info("- '{$oldValue}' to " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'") .
                         " ({$info['count']} records)");
        }

        if ($isDryRun) {
            return $totalChanges;
        }

        $this->info('Updating practice values...');
        foreach ($valuesToUpdate as $oldValue => $info) {
            SitePolygon::where('practice', $oldValue)
                ->update(['practice' => $info['new_value']]);

            $this->info("Updated {$info['count']} records from '{$oldValue}' to " .
                         ($info['new_value'] === null ? 'NULL' : "'{$info['new_value']}'"));
        }

        return $totalChanges;
    }
}

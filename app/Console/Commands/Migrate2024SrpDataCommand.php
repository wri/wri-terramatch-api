<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\SrpReport;
use App\Models\V2\Trackings\DemographicCollections;
use App\Models\V2\Trackings\Tracking;
use App\Models\V2\Trackings\TrackingEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Migrate2024SrpDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:2024-srp-data {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate 2024 SRP data from ProjectReports to SrpReports';

    /**
     * Restoration partner collections to migrate
     */
    protected array $restorationPartnerCollections = [
        DemographicCollections::DIRECT_INCOME,
        DemographicCollections::INDIRECT_INCOME,
        DemographicCollections::DIRECT_BENEFITS,
        DemographicCollections::INDIRECT_BENEFITS,
        DemographicCollections::DIRECT_CONSERVATION_PAYMENTS,
        DemographicCollections::INDIRECT_CONSERVATION_PAYMENTS,
        DemographicCollections::DIRECT_MARKET_ACCESS,
        DemographicCollections::INDIRECT_MARKET_ACCESS,
        DemographicCollections::DIRECT_CAPACITY,
        DemographicCollections::INDIRECT_CAPACITY,
        DemographicCollections::DIRECT_TRAINING,
        DemographicCollections::INDIRECT_TRAINING,
        DemographicCollections::DIRECT_LAND_TITLE,
        DemographicCollections::INDIRECT_LAND_TITLE,
        DemographicCollections::DIRECT_LIVELIHOODS,
        DemographicCollections::INDIRECT_LIVELIHOODS,
        DemographicCollections::DIRECT_PRODUCTIVITY,
        DemographicCollections::INDIRECT_PRODUCTIVITY,
        DemographicCollections::DIRECT_OTHER,
        DemographicCollections::INDIRECT_OTHER,
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Find 2024 PPC project reports (reports due Jan 3, 2025)
        // These are the reports that collected 2024 data
        $targetDueDate = Carbon::parse('2025-01-03')->startOfDay();
        $endOfDay = $targetDueDate->copy()->endOfDay();

        $this->info("Finding 2024 PPC project reports (due on {$targetDueDate->format('Y-m-d')})...");

        $projectReports = ProjectReport::where('framework_key', 'ppc')
            ->whereBetween('due_at', [$targetDueDate, $endOfDay])
            ->with(['project'])
            ->get();

        $this->info("Found {$projectReports->count()} project reports to process");

        if ($projectReports->isEmpty()) {
            $this->warn('No project reports found. Exiting.');

            return 0;
        }

        $stats = [
            'processed' => 0,
            'skipped_no_srp_report' => 0,
            'demographics_migrated' => 0,
            'entries_migrated' => 0,
            'fields_migrated' => 0,
        ];

        $progressBar = $this->output->createProgressBar($projectReports->count());
        $progressBar->start();

        DB::transaction(function () use ($projectReports, $dryRun, &$stats, $progressBar) {
            foreach ($projectReports as $projectReport) {
                try {
                    // Find corresponding 2024 SRP report
                    $srpReport = SrpReport::where('project_id', $projectReport->project_id)
                        ->where('year', 2024)
                        ->first();

                    if (! $srpReport) {
                        $stats['skipped_no_srp_report']++;
                        $progressBar->advance();

                        continue;
                    }

                    // Migrate demographics
                    [$demographicsMigrated, $entriesMigrated] = $this->migrateDemographics($projectReport, $srpReport, $dryRun);
                    $stats['demographics_migrated'] += $demographicsMigrated;
                    $stats['entries_migrated'] += $entriesMigrated;

                    // Migrate fields
                    $fieldsMigrated = $this->migrateFields($projectReport, $srpReport, $dryRun);
                    if ($fieldsMigrated) {
                        $stats['fields_migrated']++;
                    }

                    $stats['processed']++;
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("  ✗ Error processing Project Report #{$projectReport->id}: {$e->getMessage()}");
                    $this->error("    {$e->getTraceAsString()}");
                }

                $progressBar->advance();
            }
        }, 3);

        $progressBar->finish();
        $this->newLine();

        // Summary
        $this->newLine();
        $this->info('=== Migration Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Project Reports Processed', $stats['processed']],
                ['Skipped (No SRP Report)', $stats['skipped_no_srp_report']],
                ['Demographics Migrated', $stats['demographics_migrated']],
                ['Demographic Entries Migrated', $stats['entries_migrated']],
                ['SRP Reports with Fields Updated', $stats['fields_migrated']],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a DRY RUN - no changes were made');
        } else {
            $this->info('Migration completed successfully!');
        }

        return 0;
    }

    /**
     * Migrate restoration partner demographics from ProjectReport to SrpReport
     *
     * @return array{int, int} [demographics_count, entries_count]
     */
    protected function migrateDemographics(ProjectReport $projectReport, SrpReport $srpReport, bool $dryRun): array
    {
        $migrated = 0;
        $entriesMigrated = 0;

        // Find all restoration partner demographics in the project report
        $demographics = Tracking::where('demographical_type', ProjectReport::class)
            ->where('demographical_id', $projectReport->id)
            ->where('type', Tracking::RESTORATION_PARTNER_TYPE)
            ->whereIn('collection', $this->restorationPartnerCollections)
            ->get();

        if ($demographics->isEmpty()) {
            return [0, 0];
        }

        foreach ($demographics as $demographic) {
            // Check if demographic already exists in SRP report
            $existingDemographic = Tracking::where('demographical_type', SrpReport::class)
                ->where('demographical_id', $srpReport->id)
                ->where('type', $demographic->type)
                ->where('collection', $demographic->collection)
                ->first();

            if ($existingDemographic) {
                // Skip if already exists
                continue;
            }

            if (! $dryRun) {
                // Create new demographic for SRP report
                $newDemographic = Tracking::create([
                    'demographical_type' => SrpReport::class,
                    'demographical_id' => $srpReport->id,
                    'type' => $demographic->type,
                    'collection' => $demographic->collection,
                    'description' => $demographic->description,
                    'hidden' => $demographic->hidden,
                ]);

                // Migrate all entries
                $entries = TrackingEntry::where('demographic_id', $demographic->id)->get();
                $entriesCount = $entries->count();

                foreach ($entries as $entry) {
                    TrackingEntry::create([
                        'demographic_id' => $newDemographic->id,
                        'type' => $entry->type,
                        'subtype' => $entry->subtype,
                        'name' => $entry->name,
                        'amount' => $entry->amount,
                    ]);
                }

                $entriesMigrated += $entriesCount;
            } else {
                $entriesCount = TrackingEntry::where('demographic_id', $demographic->id)->count();
                $entriesMigrated += $entriesCount;
            }

            $migrated++;
        }

        return [$migrated, $entriesMigrated];
    }

    /**
     * Migrate fields: total_unique_restoration_partners and other_restoration_partners_description
     */
    protected function migrateFields(ProjectReport $projectReport, SrpReport $srpReport, bool $dryRun): bool
    {
        $updated = false;
        $updates = [];

        // Migrate total_unique_restoration_partners
        if ($projectReport->total_unique_restoration_partners !== null) {
            if ($srpReport->total_unique_restoration_partners === null) {
                $updates['total_unique_restoration_partners'] = $projectReport->total_unique_restoration_partners;
            }
        }

        // Migrate other_restoration_partners_description to restoration_partners_description
        // The description is stored in the Demographic table for direct-other or indirect-other collections
        $otherDemographic = Tracking::where('demographical_type', ProjectReport::class)
            ->where('demographical_id', $projectReport->id)
            ->where('type', Tracking::RESTORATION_PARTNER_TYPE)
            ->whereIn('collection', [
                DemographicCollections::DIRECT_OTHER,
                DemographicCollections::INDIRECT_OTHER,
            ])
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->first();

        if ($otherDemographic && ! empty($otherDemographic->description)) {
            if (empty($srpReport->restoration_partners_description)) {
                $updates['restoration_partners_description'] = $otherDemographic->description;
            }
        }

        if (! empty($updates) && ! $dryRun) {
            $srpReport->update($updates);
            $updated = true;
        } elseif (! empty($updates) && $dryRun) {
            $updated = true;
        }

        return $updated;
    }
}

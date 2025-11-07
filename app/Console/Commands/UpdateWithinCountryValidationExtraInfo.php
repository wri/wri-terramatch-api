<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateWithinCountryValidationExtraInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-within-country-validation-extra-info 
                            {--table=criteria_site : Table to update (criteria_site or criteria_site_historic)}
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update country_name in criteria_site extra_info from full names to ISO alpha-3 codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $tableName = $this->option('table');

        if (!in_array($tableName, ['criteria_site', 'criteria_site_historic'])) {
            $this->error("Invalid table name: {$tableName}. Must be 'criteria_site' or 'criteria_site_historic'.");
            return 1;
        }

        $this->info("Updating country_name in {$tableName} extra_info to ISO alpha-3 codes (criteria_id = 7)...");

        $this->info('Building country name to ISO code mapping...');
        $countryMapping = $this->buildCountryMapping();
        $this->info('Found ' . count($countryMapping) . ' countries in mapping.');

        $criteriaSites = DB::table($tableName)
            ->where('criteria_id', 7)
            ->whereNotNull('extra_info')
            ->where('extra_info', '!=', '')
            ->get(['id', 'extra_info', 'polygon_id']);

        if ($criteriaSites->isEmpty()) {
            $this->info("No {$tableName} records found with extra_info and criteria_id = 7.");
            return 0;
        }

        $this->info("Found {$criteriaSites->count()} {$tableName} records with extra_info and criteria_id = 7.");

        $recordsToUpdate = [];
        foreach ($criteriaSites as $record) {
            $extraInfo = json_decode($record->extra_info, true);
            
            if (!is_array($extraInfo) || !isset($extraInfo['country_name'])) {
                continue;
            }

            $countryName = $extraInfo['country_name'];
            
            if (preg_match('/^[A-Z]{3}$/', $countryName)) {
                continue;
            }

            $countryNameLower = strtolower($countryName);
            if (!isset($countryMapping[$countryNameLower])) {
                $this->warn("No ISO mapping found for country: {$countryName}");
                continue;
            }

            $isoCode = $countryMapping[$countryNameLower];
            $recordsToUpdate[] = [
                'id' => $record->id,
                'polygon_id' => $record->polygon_id,
                'old_name' => $countryName,
                'new_iso' => $isoCode,
                'extra_info' => $extraInfo,
            ];
        }

        if (empty($recordsToUpdate)) {
            $this->info('No records need updating. All country_name values are already in ISO format.');
            return 0;
        }

        $this->info("Found " . count($recordsToUpdate) . " records that need updating:");

        $countryGroups = collect($recordsToUpdate)->groupBy('old_name');
        foreach ($countryGroups as $countryName => $records) {
            $isoCode = $records->first()['new_iso'];
            $this->line("   - '{$countryName}' → '{$isoCode}': {$records->count()} records");
        }

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made. Sample conversions:');
            foreach (array_slice($recordsToUpdate, 0, 10) as $record) {
                $this->line("   {$tableName} ID {$record['id']}: '{$record['old_name']}' → '{$record['new_iso']}'");
            }
            if (count($recordsToUpdate) > 10) {
                $this->line("   ... and " . (count($recordsToUpdate) - 10) . " more");
            }
            return 0;
        }

        if (!$force && !$this->confirm('Do you want to proceed with the updates?')) {
            $this->info('Update cancelled.');
            return 1;
        }

        $this->info('Starting updates...');

        $successCount = 0;
        $errorCount = 0;

        DB::transaction(function () use ($recordsToUpdate, $tableName, &$successCount, &$errorCount) {
            foreach ($recordsToUpdate as $record) {
                try {
                    $extraInfo = $record['extra_info'];
                    $extraInfo['country_name'] = $record['new_iso'];

                    DB::table($tableName)
                        ->where('id', $record['id'])
                        ->update(['extra_info' => json_encode($extraInfo)]);

                    $successCount++;
                    $this->line("   ✅ {$tableName} ID {$record['id']}: '{$record['old_name']}' → '{$record['new_iso']}'");
                } catch (Exception $e) {
                    $errorCount++;
                    $this->error("   ❌ Failed to update {$tableName} ID {$record['id']}: {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        if ($successCount > 0) {
            $this->info("Successfully updated {$successCount} {$tableName} records.");
        }
        if ($errorCount > 0) {
            $this->error("Failed to update {$errorCount} {$tableName} records.");
        }

        $this->info('Update completed!');

        return $errorCount > 0 ? 1 : 0;
    }

    private function buildCountryMapping(): array
    {
        $countries = DB::table('world_countries_generalized')
            ->whereNotNull('country')
            ->whereNotNull('iso')
            ->get(['country', 'iso']);

        $mapping = [];
        foreach ($countries as $country) {
            $countryNameLower = strtolower($country->country);
            $mapping[$countryNameLower] = $country->iso;
        }

        return $mapping;
    }
}

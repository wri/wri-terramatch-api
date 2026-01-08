<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlignSlugOptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:align-slug-options {--dry-run : Show what would be changed without making actual changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Align slug options for various TM fields according to TM-2723';

    /**
     * Mapping of field slugs to transform
     * Format: ['table.field' => ['old_slug' => 'new_slug']]
     */
    protected array $fieldMappings = [
        // organisations.type
        'organisations.type' => [
            'government' => 'government-agency',
            'non-profit-organisation' => 'non-profit-organization', // Fix typo variant
        ],
        // v2_projects.land_tenure_project_area
        'v2_projects.land_tenure_project_area' => [
            'common-land' => 'communal-land',
            'communal' => 'communal-land',
            'indigenous' => 'indigenous-land',
            'national_protected_area' => 'national-protected-area',
            'other' => 'other-land',
            'private' => 'private-land',
            'public' => 'public-land',
        ],
        // project_pitches.land_tenure_proj_area
        'project_pitches.land_tenure_proj_area' => [
            'common-land' => 'communal-land',
            'communal' => 'communal-land',
            'indigenous' => 'indigenous-land',
            'indigenous-lands' => 'indigenous-land',
            'national_protected_area' => 'national-protected-area',
            'other' => 'other-land',
            'private' => 'private-land',
            'private-lands' => 'private-land',
            'public' => 'public-land',
            'public-land' => 'public-land', // Already correct, but included for completeness
        ],
        // v2_sites.land_tenures
        'v2_sites.land_tenures' => [
            'communal' => 'communal-land',
            'community-lands' => 'communal-land',
            'national_protected_area' => 'national-protected-area',
        ],
        // v2_sites.siting_strategy
        'v2_sites.siting_strategy' => [
            'concentred' => 'concentrated',
        ],
        // v2_nurseries.type
        'v2_nurseries.type' => [
            'manageing' => 'managing',
        ],
        // project_pitches.land_use_types
        'project_pitches.land_use_types' => [
            'riparian-area-wetland-or-mangrove' => 'riparian-area-or-wetland',
        ],
    ];

    /**
     * Option list slug mappings
     * Format: ['option_list_key' => ['old_slug' => 'new_slug']]
     */
    protected array $optionListMappings = [
        'land-tenures' => [
            'communal' => 'communal-land',
            'common-land' => 'communal-land',
            'indigenous' => 'indigenous-land',
            'indigenous-lands' => 'indigenous-land',
            'national_protected_area' => 'national-protected-area',
            'other' => 'other-land',
            'private' => 'private-land',
            'private-lands' => 'private-land',
            'public' => 'public-land',
            'community-lands' => 'communal-land',
        ],
        'land-tenures-brazil' => [
            'communal' => 'communal-land',
            'common-land' => 'communal-land',
            'indigenous' => 'indigenous-land',
            'indigenous-lands' => 'indigenous-land',
            'national_protected_area' => 'national-protected-area',
            'other' => 'other-land',
            'private' => 'private-land',
            'private-lands' => 'private-land',
            'public' => 'public-land',
            'community-lands' => 'communal-land',
        ],
        'siting-strategies' => [
            'concentred' => 'concentrated',
        ],
        'nursery-type' => [
            'manageing' => 'managing',
        ],
        'land-use-systems' => [
            'riparian-area-wetland-or-mangrove' => 'riparian-area-or-wetland',
        ],
        'organisation-type' => [
            'government' => 'government-agency',
            'non-profit-organisation' => 'non-profit-organization',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting slug alignment for TM-2723...');
        $this->newLine();

        $stats = [
            'database_updates' => 0,
            'option_list_updates' => 0,
            'duplicates_removed' => 0,
            'errors' => 0,
        ];

        DB::beginTransaction();

        try {
            // Update database fields
            $this->updateDatabaseFields($isDryRun, $stats);

            // Update FormOptionListOption slugs
            $this->updateOptionListSlugs($isDryRun, $stats);

            // Update v2_funding_types.type (convert all snake_case to kebab-case)
            $this->updateFundingTypes($isDryRun, $stats);

            if ($isDryRun) {
                DB::rollBack();
                $this->newLine();
                $this->info('DRY RUN completed. No changes were made.');
            } else {
                DB::commit();
                $this->newLine();
                $this->info('✓ Slug alignment completed successfully!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during migration: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            $stats['errors']++;

            return 1;
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Database records updated: {$stats['database_updates']}");
        $this->line("  Option list options updated: {$stats['option_list_updates']}");
        $this->line("  Duplicates removed: {$stats['duplicates_removed']}");
        if ($stats['errors'] > 0) {
            $this->warn("  Errors encountered: {$stats['errors']}");
        }

        return 0;
    }

    /**
     * Update database fields with new slug values
     */
    protected function updateDatabaseFields(bool $dryRun, array &$stats): void
    {
        $this->info('Updating database fields...');

        foreach ($this->fieldMappings as $fieldKey => $mappings) {
            [$table, $field] = explode('.', $fieldKey);

            $this->line("  Processing {$table}.{$field}...");

            // Check if field is JSON array or string
            $isJsonArray = in_array($fieldKey, [
                'v2_projects.land_tenure_project_area',
                'project_pitches.land_tenure_proj_area',
                'v2_sites.land_tenures',
                'project_pitches.land_use_types',
            ]);

            foreach ($mappings as $oldSlug => $newSlug) {
                try {
                    if ($isJsonArray) {
                        // Handle JSON array fields
                        $updated = $this->updateJsonArrayField($table, $field, $oldSlug, $newSlug, $dryRun, $stats);
                    } else {
                        // Handle string fields
                        $updated = $this->updateStringField($table, $field, $oldSlug, $newSlug, $dryRun);
                    }

                    if ($updated > 0) {
                        $stats['database_updates'] += $updated;
                        $this->line("    ✓ Updated {$updated} records: '{$oldSlug}' → '{$newSlug}'");
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->error("    ✗ Error updating '{$oldSlug}' → '{$newSlug}': " . $e->getMessage());
                }
            }
        }

        // Remove duplicates from JSON array fields after all transformations
        $this->newLine();
        $this->info('Removing duplicates from JSON array fields...');
        foreach ($this->fieldMappings as $fieldKey => $mappings) {
            [$table, $field] = explode('.', $fieldKey);
            $isJsonArray = in_array($fieldKey, [
                'v2_projects.land_tenure_project_area',
                'project_pitches.land_tenure_proj_area',
                'v2_sites.land_tenures',
                'project_pitches.land_use_types',
            ]);

            if ($isJsonArray) {
                $this->removeDuplicatesFromJsonArray($table, $field, $dryRun, $stats);
            }
        }
    }

    /**
     * Update JSON array field values
     */
    protected function updateJsonArrayField(string $table, string $field, string $oldSlug, string $newSlug, bool $dryRun, array &$stats): int
    {
        if ($dryRun) {
            // Count how many records would be affected
            $records = DB::table($table)
                ->whereNotNull($field)
                ->get();

            $count = 0;
            foreach ($records as $record) {
                $value = json_decode($record->$field, true);
                if (is_array($value) && in_array($oldSlug, $value)) {
                    $count++;
                }
            }

            return $count;
        }

        // Get all records with this field
        $records = DB::table($table)
            ->whereNotNull($field)
            ->get();

        $updated = 0;
        foreach ($records as $record) {
            $value = json_decode($record->$field, true);

            if (! is_array($value)) {
                continue;
            }

            $changed = false;
            $newValue = [];
            foreach ($value as $item) {
                if ($item === $oldSlug) {
                    $newValue[] = $newSlug;
                    $changed = true;
                } else {
                    $newValue[] = $item;
                }
            }

            if ($changed) {
                // Remove duplicates and re-index
                $newValue = array_values(array_unique($newValue));

                DB::table($table)
                    ->where('id', $record->id)
                    ->update([$field => json_encode($newValue)]);

                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Remove duplicates from JSON array fields
     */
    protected function removeDuplicatesFromJsonArray(string $table, string $field, bool $dryRun, array &$stats): void
    {
        $this->line("  Removing duplicates from {$table}.{$field}...");

        $records = DB::table($table)
            ->whereNotNull($field)
            ->get();

        $duplicatesRemoved = 0;
        $recordsWithDuplicates = 0;

        foreach ($records as $record) {
            $value = json_decode($record->$field, true);

            if (! is_array($value)) {
                continue;
            }

            $uniqueValue = array_values(array_unique($value));

            // Check if duplicates were removed
            if (count($value) !== count($uniqueValue)) {
                $recordsWithDuplicates++;
                $duplicatesRemoved += (count($value) - count($uniqueValue));

                if (! $dryRun) {
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update([$field => json_encode($uniqueValue)]);
                }
            }
        }

        if ($duplicatesRemoved > 0) {
            $stats['duplicates_removed'] += $duplicatesRemoved;
            if ($dryRun) {
                $this->line("    ⊙ Would remove {$duplicatesRemoved} duplicate entries from {$recordsWithDuplicates} records");
            } else {
                $this->line("    ✓ Removed {$duplicatesRemoved} duplicate entries from {$recordsWithDuplicates} records");
            }
        } else {
            $this->line('    ⊙ No duplicates found');
        }
    }

    /**
     * Update string field values
     */
    protected function updateStringField(string $table, string $field, string $oldSlug, string $newSlug, bool $dryRun): int
    {
        if ($dryRun) {
            return DB::table($table)
                ->where($field, $oldSlug)
                ->count();
        }

        return DB::table($table)
            ->where($field, $oldSlug)
            ->update([$field => $newSlug]);
    }

    /**
     * Update FormOptionListOption slugs
     */
    protected function updateOptionListSlugs(bool $dryRun, array &$stats): void
    {
        $this->newLine();
        $this->info('Updating FormOptionListOption slugs...');

        foreach ($this->optionListMappings as $optionListKey => $mappings) {
            $this->line("  Processing option list: {$optionListKey}...");

            $optionList = FormOptionList::where('key', $optionListKey)->first();

            if (! $optionList) {
                $this->line("    ⊙ Option list '{$optionListKey}' not found (may not exist in this environment), skipping...");

                continue;
            }

            // Get all existing slugs for debugging
            $existingSlugs = FormOptionListOption::where('form_option_list_id', $optionList->id)
                ->pluck('slug')
                ->toArray();

            foreach ($mappings as $oldSlug => $newSlug) {
                try {
                    $option = FormOptionListOption::where('form_option_list_id', $optionList->id)
                        ->where('slug', $oldSlug)
                        ->first();

                    if ($option) {
                        // Check if new slug already exists (could cause conflict)
                        $conflictingOption = FormOptionListOption::where('form_option_list_id', $optionList->id)
                            ->where('slug', $newSlug)
                            ->where('id', '!=', $option->id)
                            ->first();

                        if ($conflictingOption) {
                            $this->warn("    ⚠ Cannot update '{$oldSlug}' → '{$newSlug}': slug '{$newSlug}' already exists (ID: {$conflictingOption->id})");
                            $stats['errors']++;

                            continue;
                        }

                        if ($dryRun) {
                            $this->line("    ⊙ Would update option ID {$option->id}: '{$oldSlug}' → '{$newSlug}'");
                        } else {
                            $option->slug = $newSlug;
                            $option->save();
                            $stats['option_list_updates']++;
                            $this->line("    ✓ Updated option ID {$option->id}: '{$oldSlug}' → '{$newSlug}'");
                        }
                    } else {
                        // Check if already updated (new slug exists)
                        $existing = FormOptionListOption::where('form_option_list_id', $optionList->id)
                            ->where('slug', $newSlug)
                            ->first();

                        if ($existing) {
                            $this->line("    ⊙ Slug '{$oldSlug}' not found, but '{$newSlug}' already exists (ID: {$existing->id}) - already updated");
                        } else {
                            // Check if old slug might exist with different casing or similar
                            $similar = FormOptionListOption::where('form_option_list_id', $optionList->id)
                                ->whereRaw('LOWER(slug) = ?', [strtolower($oldSlug)])
                                ->first();

                            if ($similar && $similar->slug !== $oldSlug) {
                                $this->line("    ⊙ Slug '{$oldSlug}' not found, but found similar '{$similar->slug}' (ID: {$similar->id})");
                            } else {
                                $this->line("    ⊙ Slug '{$oldSlug}' not found in '{$optionListKey}' (may not exist in this environment)");
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->error("    ✗ Error updating option '{$oldSlug}' → '{$newSlug}': " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Update v2_funding_types.type - convert all snake_case to kebab-case
     */
    protected function updateFundingTypes(bool $dryRun, array &$stats): void
    {
        $this->newLine();
        $this->info('Updating v2_funding_types.type (snake_case → kebab-case)...');

        // Get all distinct type values that contain underscores
        $typesWithUnderscores = DB::table('v2_funding_types')
            ->select('type')
            ->distinct()
            ->where('type', 'LIKE', '%_%')
            ->get();

        if ($typesWithUnderscores->isEmpty()) {
            $this->line('    ⊙ No records found with snake_case type values.');

            return;
        }

        $conversionMap = [];
        foreach ($typesWithUnderscores as $type) {
            $originalType = $type->type;
            $kebabCaseType = str_replace('_', '-', $originalType);
            $conversionMap[$originalType] = $kebabCaseType;
        }

        $this->info('    Found ' . count($conversionMap) . ' distinct type values to convert:');

        foreach ($conversionMap as $originalType => $kebabCaseType) {
            $this->line("      '{$originalType}' → '{$kebabCaseType}'");
        }

        if ($dryRun) {
            $totalCount = 0;
            foreach ($conversionMap as $originalType => $kebabCaseType) {
                $count = DB::table('v2_funding_types')
                    ->where('type', $originalType)
                    ->count();
                $totalCount += $count;
            }
            $this->line("    ⊙ Would convert {$totalCount} records");

            return;
        }

        $totalUpdated = 0;
        foreach ($conversionMap as $originalType => $kebabCaseType) {
            try {
                $updated = DB::table('v2_funding_types')
                    ->where('type', $originalType)
                    ->update(['type' => $kebabCaseType]);

                $totalUpdated += $updated;
                $stats['database_updates'] += $updated;
                $this->line("    ✓ Updated {$updated} records: '{$originalType}' → '{$kebabCaseType}'");
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("    ✗ Error converting '{$originalType}': " . $e->getMessage());
            }
        }

        $this->line("    ✓ Total funding type records updated: {$totalUpdated}");
    }
}

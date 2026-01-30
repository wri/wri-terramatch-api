<?php

namespace App\Console\Commands;

use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlignFormOptionListOptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'align:form-option-list-options {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Align form_option_list_options slugs and update references in entities';

    /**
     * Mapping of old slugs to new slugs
     * Format: [option_id => ['old_slug' => 'new_slug', 'new_label' => 'Label']]
     */
    protected array $slugMappings = [
        375 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        420 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        461 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        470 => ['old_slug' => 'undesignated-public-lands', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        376 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        421 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        460 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        471 => ['old_slug' => 'private-lands', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        377 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        422 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        463 => ['old_slug' => 'indigenous-lands', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        378 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        423 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        462 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        469 => ['old_slug' => 'quilombola-lands', 'new_slug' => 'quilombola-lands', 'new_label' => 'Quilombola Lands'],
        412 => ['old_slug' => 'manageing', 'new_slug' => 'managing', 'new_label' => 'Managing'],
    ];

    /**
     * This will be populated dynamically based on the form_option_list keys
     * that contain the options we're updating
     */
    protected array $entityColumns = [];

    /**
     * Form option list keys that contain the options we're updating
     */
    protected array $optionListKeys = [];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting alignment of form_option_list_options slugs...');
        $this->newLine();

        $stats = [
            'options_updated' => 0,
            'form_question_options_updated' => 0,
            'entities_updated' => 0,
            'total_values_updated' => 0,
            'errors' => 0,
        ];

        DB::transaction(function () use ($dryRun, &$stats) {
            $this->info('Step 1: Identifying form_option_list keys...');
            $this->identifyOptionListKeys();

            $this->newLine();
            $this->info('Step 2: Updating form_option_list_options slugs...');
            $this->updateOptionSlugs($dryRun, $stats);

            $this->newLine();
            $this->info('Step 3: Updating form_question_options slugs...');
            $this->updateFormQuestionOptions($dryRun, $stats);

            $this->newLine();
            $this->info('Step 4: Updating entity values...');
            $this->updateEntityValues($dryRun, $stats);
        }, 3);

        $this->newLine();
        $this->info('=== Alignment Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Option Slugs Updated', $stats['options_updated']],
                ['Form Question Options Updated', $stats['form_question_options_updated']],
                ['Entities Updated', $stats['entities_updated']],
                ['Total Values Updated', $stats['total_values_updated']],
                ['Errors', $stats['errors']],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a DRY RUN - no changes were made');
        } else {
            $this->info('Alignment completed successfully!');
        }

        return 0;
    }

    /**
     * Identify which form_option_list keys contain the options we're updating
     * and map them to entity columns from linked-fields.php
     */
    protected function identifyOptionListKeys(): void
    {
        // Get all unique form_option_list keys for the options we're updating
        // Query directly from database without using relationship
        $optionIds = array_keys($this->slugMappings);

        $options = DB::table('form_option_list_options')
            ->whereIn('id', $optionIds)
            ->select('id', 'form_option_list_id')
            ->get();

        $formOptionListIds = $options->pluck('form_option_list_id')->unique()->toArray();

        if (! empty($formOptionListIds)) {
            $formOptionLists = DB::table('form_option_lists')
                ->whereIn('id', $formOptionListIds)
                ->select('id', 'key')
                ->get();

            $this->optionListKeys = $formOptionLists->pluck('key')->unique()->toArray();
        }

        $this->line('  Found option_list keys: ' . implode(', ', $this->optionListKeys));
        $this->newLine();

        $linkedFieldsConfig = config('wri.linked-fields.models', []);

        foreach ($linkedFieldsConfig as $modelKey => $modelConfig) {
            if (! isset($modelConfig['fields'])) {
                continue;
            }

            foreach ($modelConfig['fields'] as $fieldKey => $fieldConfig) {
                $optionListKey = $fieldConfig['option_list_key'] ?? null;
                if (! $optionListKey || ! in_array($optionListKey, $this->optionListKeys)) {
                    continue;
                }

                $property = $fieldConfig['property'] ?? null;
                if (! $property) {
                    continue;
                }

                $modelClass = $modelConfig['model'] ?? null;
                if (! $modelClass || ! class_exists($modelClass)) {
                    continue;
                }

                // Normalize model class name (handle string or class)
                if (is_string($modelClass)) {
                    $modelClass = ltrim($modelClass, '\\');
                }

                if (! isset($this->entityColumns[$modelClass])) {
                    $this->entityColumns[$modelClass] = [];
                }

                if (! in_array($property, $this->entityColumns[$modelClass])) {
                    $this->entityColumns[$modelClass][] = $property;
                    $this->line("  ✓ Mapped: {$optionListKey} -> {$modelClass}::{$property}");
                }
            }
        }

        if (empty($this->entityColumns)) {
            $this->warn('  ⚠ No entity columns found for the identified option_list keys');
        }
    }

    /**
     * Update slugs in form_option_list_options table
     */
    protected function updateOptionSlugs(bool $dryRun, array &$stats): void
    {
        $progressBar = $this->output->createProgressBar(count($this->slugMappings));
        $progressBar->start();

        foreach ($this->slugMappings as $optionId => $mapping) {
            try {
                $option = FormOptionListOption::find($optionId);

                if (! $option) {
                    $this->newLine();
                    $this->warn("  ⚠ Option ID {$optionId} not found, skipping...");
                    $stats['errors']++;
                    $progressBar->advance();

                    continue;
                }

                if ($option->slug === $mapping['old_slug']) {
                    if (! $dryRun) {
                        $option->slug = $mapping['new_slug'];
                        if (! empty($mapping['new_label'])) {
                            $option->label = $mapping['new_label'];
                        }
                        $option->save();
                    }

                    $this->newLine();
                    $this->line("  ✓ Updated option ID {$optionId}: '{$mapping['old_slug']}' → '{$mapping['new_slug']}'");
                    $stats['options_updated']++;
                } elseif ($option->slug === $mapping['new_slug']) {
                    $this->newLine();
                    $this->line("  ⊙ Option ID {$optionId} already has slug '{$mapping['new_slug']}', skipping...");
                } else {
                    $this->newLine();
                    $this->warn("  ⚠ Option ID {$optionId} has unexpected slug '{$option->slug}', expected '{$mapping['old_slug']}'");
                    $stats['errors']++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  ✗ Error updating option ID {$optionId}: {$e->getMessage()}");
                $stats['errors']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
    }

    /**
     * Update slugs in form_question_options table
     * These are the options stored when forms are saved
     * Follows the hierarchy: Forms -> Sections -> Questions -> FormQuestionOptions
     */
    protected function updateFormQuestionOptions(bool $dryRun, array &$stats): void
    {
        if (empty($this->optionListKeys)) {
            $this->line('  ⊙ No option_list keys identified, skipping form_question_options update');

            return;
        }

        // Step 1: Find all form_questions that use the option_list keys we're updating
        $formQuestionIds = DB::table('form_questions')
            ->whereIn('options_list', $this->optionListKeys)
            ->whereNotNull('options_list')
            ->pluck('id')
            ->toArray();

        if (empty($formQuestionIds)) {
            $this->line('  ⊙ No form_questions found with matching options_list keys');

            return;
        }

        $this->line('  Found ' . count($formQuestionIds) . ' form_questions with matching options_list');

        // Step 2: Find all form_question_options for these questions
        $formQuestionOptions = DB::table('form_question_options')
            ->whereIn('form_question_id', $formQuestionIds)
            ->get();

        if ($formQuestionOptions->isEmpty()) {
            $this->line('  ⊙ No form_question_options found for these questions');

            return;
        }

        $this->line("  Found {$formQuestionOptions->count()} form_question_options to check");
        $progressBar = $this->output->createProgressBar($formQuestionOptions->count());
        $progressBar->start();

        foreach ($formQuestionOptions as $formQuestionOption) {
            try {
                $currentSlug = $formQuestionOption->slug;
                $updated = false;
                $mappingToApply = null;

                // Check if the current slug matches any of our old slugs
                foreach ($this->slugMappings as $mapping) {
                    if ($currentSlug === $mapping['old_slug']) {
                        $mappingToApply = $mapping;
                        $updated = true;

                        break;
                    }
                }

                if ($updated && $mappingToApply) {
                    if (! $dryRun) {
                        $updateData = ['slug' => $mappingToApply['new_slug']];

                        // Also update label if provided
                        if (! empty($mappingToApply['new_label'])) {
                            $updateData['label'] = $mappingToApply['new_label'];
                        }

                        DB::table('form_question_options')
                            ->where('id', $formQuestionOption->id)
                            ->update($updateData);
                    }

                    $stats['form_question_options_updated']++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  ✗ Error updating form_question_option ID {$formQuestionOption->id}: {$e->getMessage()}");
                $stats['errors']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->line("  ✓ Updated {$stats['form_question_options_updated']} form_question_options");
    }

    /**
     * Update entity values that reference the old slugs
     */
    protected function updateEntityValues(bool $dryRun, array &$stats): void
    {
        foreach ($this->entityColumns as $modelClass => $columns) {
            $this->line("Processing {$modelClass}...");

            foreach ($columns as $column) {
                $this->updateColumnValues($modelClass, $column, $dryRun, $stats);
            }
        }
    }

    /**
     * Update values in a specific column for a model
     * Handles both JSON array columns and string columns
     */
    protected function updateColumnValues(string $modelClass, string $column, bool $dryRun, array &$stats): void
    {
        $model = new $modelClass();
        $table = $model->getTable();

        $records = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->get();

        if ($records->isEmpty()) {
            $this->line("  ⊙ No records found in {$table}.{$column}");

            return;
        }

        $updatedCount = 0;
        $valuesUpdated = 0;

        foreach ($records as $record) {
            try {
                $currentValue = $record->$column;
                $updated = false;
                $newValue = null;

                if (is_string($currentValue)) {
                    $decoded = json_decode($currentValue, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $newValue = $decoded;

                        foreach ($newValue as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $nestedKey => $nestedValue) {
                                    if (is_string($nestedValue)) {
                                        foreach ($this->slugMappings as $mapping) {
                                            if ($nestedValue === $mapping['old_slug']) {
                                                $newValue[$key][$nestedKey] = $mapping['new_slug'];
                                                $updated = true;
                                                $valuesUpdated++;
                                            }
                                        }
                                    }
                                }
                            } elseif (is_string($value)) {
                                foreach ($this->slugMappings as $mapping) {
                                    if ($value === $mapping['old_slug']) {
                                        $newValue[$key] = $mapping['new_slug'];
                                        $updated = true;
                                        $valuesUpdated++;
                                    }
                                }
                            }
                        }

                        if ($updated) {
                            $newValue = json_encode($newValue);
                        }
                    } elseif (is_string($currentValue)) {
                        $newValue = $currentValue;
                        foreach ($this->slugMappings as $mapping) {
                            if ($currentValue === $mapping['old_slug']) {
                                $newValue = $mapping['new_slug'];
                                $updated = true;
                                $valuesUpdated++;

                                break;
                            }
                        }
                    }
                }

                if ($updated && $newValue !== null) {
                    if (! $dryRun) {
                        DB::table($table)
                            ->where('id', $record->id)
                            ->update([
                                $column => $newValue,
                            ]);
                    }

                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $this->warn("  ✗ Error updating record ID {$record->id} in {$table}.{$column}: {$e->getMessage()}");
                $stats['errors']++;
            }
        }

        if ($updatedCount > 0) {
            $this->line("  ✓ Updated {$updatedCount} records in {$table}.{$column} ({$valuesUpdated} values changed)");
            $stats['entities_updated'] += $updatedCount;
            $stats['total_values_updated'] += $valuesUpdated;
        } else {
            $this->line("  ⊙ No updates needed in {$table}.{$column}");
        }
    }
}

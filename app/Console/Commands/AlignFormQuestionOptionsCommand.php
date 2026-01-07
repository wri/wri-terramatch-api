<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlignFormQuestionOptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'align:form-question-options {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Align form_question_options slugs and labels';

    /**
     * Mapping of old slugs to new slugs
     * Format: [option_id => ['old_slug' => 'new_slug', 'new_label' => 'Label']]
     */
    protected array $slugMappings = [
        // communal -> communal-land
        11870 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        3711 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        2709 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        2426 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        4395 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        11590 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14399 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14421 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14439 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14459 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14480 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14499 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14524 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14539 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14559 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        14579 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        // community-lands -> communal-land
        5657 => ['old_slug' => 'community-lands', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        // communal-lands -> communal-land
        5662 => ['old_slug' => 'communal-lands', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        // common-land -> communal-land
        1281 => ['old_slug' => 'common-land', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        1087 => ['old_slug' => 'common-land', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],

        // leashold-lands -> leasehold-land
        5590 => ['old_slug' => 'leasehold-lands', 'new_slug' => 'leasehold-land', 'new_label' => 'Leasehold Land'],
        5663 => ['old_slug' => 'leashold-lands', 'new_slug' => 'leasehold-land', 'new_label' => 'Leasehold Land'],
        // indigenous -> indigenous-land
        11869 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        3710 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        2708 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        2425 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        4394 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        11589 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14398 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14419 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14438 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14458 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14478 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14498 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14522 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14538 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14558 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        14578 => ['old_slug' => 'indigenous', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        // indigenous-lands -> indigenous-land
        21138 => ['old_slug' => 'indigenous-lands', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        21054 => ['old_slug' => 'indigenous-lands', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        // other -> other-land
        5591 => ['old_slug' => 'other', 'new_slug' => 'other-land', 'new_label' => 'Other Land'],
        4374 => ['old_slug' => 'other', 'new_slug' => 'other-land', 'new_label' => 'Other Land'],
        5640 => ['old_slug' => 'other', 'new_slug' => 'other-land', 'new_label' => 'Other Land'],
        21147 => ['old_slug' => 'other', 'new_slug' => 'other-land', 'new_label' => 'Other Land'],
        229 => ['old_slug' => 'other', 'new_slug' => 'other-land', 'new_label' => 'Other Land'],
        141 => ['old_slug' => 'other', 'new_slug' => 'other-land', 'new_label' => 'Other Land'],
        21063 => ['old_slug' => 'other', 'new_slug' => 'other-land', 'new_label' => 'Other Land'],
        // private-lands -> private-land
        21146 => ['old_slug' => 'private-lands', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        21062 => ['old_slug' => 'private-lands', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        // private -> private-land
        5588 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        11868 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        3709 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        2707 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        2424 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        4393 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        5636 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        11588 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14397 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14417 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14437 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14457 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14477 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14497 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14520 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14537 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14557 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        14577 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        // public -> public-land
        5587 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        11867 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        3708 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        2706 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        2423 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        4392 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        5661 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        11587 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14396 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14416 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14436 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14456 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14476 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14496 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14518 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14536 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14556 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        14576 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        // undesignated-public-lands -> public-land
        21145 => ['old_slug' => 'undesignated-public-lands', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        21061 => ['old_slug' => 'undesignated-public-lands', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        // concentred -> concentrated
        14430 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        11592 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14461 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        5641 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14581 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14532 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14441 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        4668 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14401 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14501 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14561 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14541 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        14490 => ['old_slug' => 'concentred', 'new_slug' => 'concentrated', 'new_label' => 'Concentrated'],
        // manageing -> managing
        2416 => ['old_slug' => 'manageing', 'new_slug' => 'managing', 'new_label' => 'Managing'],
        11597 => ['old_slug' => 'manageing', 'new_slug' => 'managing', 'new_label' => 'Managing'],
        21183 => ['old_slug' => 'manageing', 'new_slug' => 'managing', 'new_label' => 'Managing'],
        14383 => ['old_slug' => 'manageing', 'new_slug' => 'managing', 'new_label' => 'Managing'],
        // riparian-area-wetland-or-mangrove -> riparian-area-or-wetland
        21078 => ['old_slug' => 'riparian-area-wetland-or-mangrove', 'new_slug' => 'riparian-area-or-wetland', 'new_label' => 'Riparian Area or Wetland'],
        // quilombola-lands -> quilombola-land
        21144 => ['old_slug' => 'quilombola-lands', 'new_slug' => 'quilombola-land', 'new_label' => 'Quilombola Land'],
        21060 => ['old_slug' => 'quilombola-lands', 'new_slug' => 'quilombola-land', 'new_label' => 'Quilombola Land'],

        21192 => ['old_slug' => 'communal', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        227 => ['old_slug' => 'communal-land', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        139 => ['old_slug' => 'communal-land', 'new_slug' => 'communal-land', 'new_label' => 'Communal Land'],
        226 => ['old_slug' => 'indigenous-land', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        138 => ['old_slug' => 'indigenous-land', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        21100 => ['old_slug' => 'indigenous-lands', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        21016 => ['old_slug' => 'indigenous-lands', 'new_slug' => 'indigenous-land', 'new_label' => 'Indigenous Land'],
        224 => ['old_slug' => 'private-land', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        136 => ['old_slug' => 'private-land', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        1085 => ['old_slug' => 'private-land', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        21108 => ['old_slug' => 'private-lands', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        21024 => ['old_slug' => 'private-lands', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
        225 => ['old_slug' => 'public-land', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        137 => ['old_slug' => 'public-land', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        21107 => ['old_slug' => 'undesignated-public-lands', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        21023 => ['old_slug' => 'undesignated-public-lands', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        21189 => ['old_slug' => 'public', 'new_slug' => 'public-land', 'new_label' => 'Public Land'],
        21106 => ['old_slug' => 'quilombola-lands', 'new_slug' => 'quilombola-land', 'new_label' => 'Quilombola Land'],
        21022 => ['old_slug' => 'quilombola-lands', 'new_slug' => 'quilombola-land', 'new_label' => 'Quilombola Land'],

        21190 => ['old_slug' => 'private', 'new_slug' => 'private-land', 'new_label' => 'Private Land'],
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

        $this->info('Starting alignment of form_question_options slugs...');
        $this->newLine();

        $stats = [
            'options_updated' => 0,
            'entities_updated' => 0,
            'total_values_updated' => 0,
            'errors' => 0,
        ];

        DB::transaction(function () use ($dryRun, &$stats) {
            $this->info('Step 1: Identifying form_option_list keys...');
            $this->identifyOptionListKeys();

            $this->newLine();
            $this->info('Step 2: Updating form_question_options slugs...');
            $this->updateFormQuestionOptions($dryRun, $stats);

            $this->newLine();
            $this->info('Step 3: Updating entity values...');
            $this->updateEntityValues($dryRun, $stats);
        }, 3);

        $this->newLine();
        $this->info('=== Alignment Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Option Slugs Updated', $stats['options_updated']],
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
     * Update slugs in form_question_options table
     */
    protected function updateFormQuestionOptions(bool $dryRun, array &$stats): void
    {
        $progressBar = $this->output->createProgressBar(count($this->slugMappings));
        $progressBar->start();

        foreach ($this->slugMappings as $optionId => $mapping) {
            try {
                $formQuestionOption = DB::table('form_question_options')
                    ->where('id', $optionId)
                    ->first();

                if (! $formQuestionOption) {
                    $this->newLine();
                    $this->warn("  ⚠ Option ID {$optionId} not found, skipping...");
                    $stats['errors']++;
                    $progressBar->advance();

                    continue;
                }

                if ($formQuestionOption->slug === $mapping['old_slug']) {
                    if (! $dryRun) {
                        $updateData = ['slug' => $mapping['new_slug']];
                        if (! empty($mapping['new_label'])) {
                            $updateData['label'] = $mapping['new_label'];
                        }

                        DB::table('form_question_options')
                            ->where('id', $optionId)
                            ->update($updateData);
                    }

                    $this->newLine();
                    $this->line("  ✓ Updated option ID {$optionId}: '{$mapping['old_slug']}' → '{$mapping['new_slug']}'");
                    $stats['options_updated']++;
                } elseif ($formQuestionOption->slug === $mapping['new_slug']) {
                    $this->newLine();
                    $this->line("  ⊙ Option ID {$optionId} already has slug '{$mapping['new_slug']}', skipping...");
                } else {
                    $this->newLine();
                    $this->warn("  ⚠ Option ID {$optionId} has unexpected slug '{$formQuestionOption->slug}', expected '{$mapping['old_slug']}'");
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
     * Identify which form_option_list keys contain the options we're updating
     * and map them to entity columns from linked-fields.php
     * Follows the hierarchy: form_question_options -> form_questions -> options_list
     */
    protected function identifyOptionListKeys(): void
    {
        $optionIds = array_keys($this->slugMappings);

        // Get form_question_ids from form_question_options
        $formQuestionOptions = DB::table('form_question_options')
            ->whereIn('id', $optionIds)
            ->select('form_question_id')
            ->distinct()
            ->get();

        $formQuestionIds = $formQuestionOptions->pluck('form_question_id')->unique()->toArray();

        if (empty($formQuestionIds)) {
            $this->warn('  ⚠ No form_questions found for the specified form_question_options');

            return;
        }

        // Get options_list keys from form_questions
        $formQuestions = DB::table('form_questions')
            ->whereIn('id', $formQuestionIds)
            ->whereNotNull('options_list')
            ->select('options_list')
            ->distinct()
            ->get();

        $this->optionListKeys = $formQuestions->pluck('options_list')->unique()->toArray();

        $this->line('  Found option_list keys: ' . implode(', ', $this->optionListKeys));
        $this->newLine();

        // Map option_list_key to entity columns from linked-fields.php
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

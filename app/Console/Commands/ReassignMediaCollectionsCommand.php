<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReassignMediaCollectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:reassign-collections 
                            {--file=imports/file_collection_reassignment.csv : Path to CSV file}
                            {--dry-run : Preview changes without applying them}
                            {--skip-validation : Skip file name and model type validation}
                            {--chunk=100 : Number of records to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassign media files to new collection names based on CSV data';

    /**
     * Valid collection names for ProjectReport model
     */
    private array $validCollections = [
        'media',
        'socioeconomic_benefits',
        'file',
        'other_additional_documents',
        'photos',
        'baseline_report_upload',
        'local_governance_order_letter_upload',
        'events_meetings_photos',
        'local_governance_proof_of_partnership_upload',
        'top_three_successes_upload',
        'direct_jobs_upload',
        'convergence_jobs_upload',
        'convergence_schemes_upload',
        'livelihood_activities_upload',
        'direct_livelihood_impacts_upload',
        'certified_database_upload',
        'physical_assets_photos',
        'indirect_community_partners_upload',
        'training_capacity_building_upload',
        'training_capacity_building_photos',
        'financial_report_upload',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvPath = $this->option('file');
        $fullPath = base_path($csvPath);
        $isDryRun = $this->option('dry-run');
        $skipValidation = $this->option('skip-validation');
        $chunkSize = (int) $this->option('chunk');

        if (! file_exists($fullPath) || ! is_readable($fullPath)) {
            $this->error("CSV file not found or not readable at: $fullPath");

            return 1;
        }

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No changes will be made to the database');
        }

        $this->info("Starting media collection reassignment from: $csvPath");

        $csvData = $this->readCsvFile($fullPath);

        if (empty($csvData)) {
            $this->error('No data found in CSV file');

            return 1;
        }

        $totalRows = count($csvData);
        $this->info("Found $totalRows records to process");

        $requiredColumns = ['uuid', 'new_collection_name'];
        $validationResult = $this->validateCsvStructure($csvData, $requiredColumns);

        if (! $validationResult['valid']) {
            $this->error($validationResult['error']);

            return 1;
        }

        $this->info('Configuration:');
        $this->info('  - Dry run: ' . ($isDryRun ? 'Yes' : 'No'));
        $this->info('  - Skip validation: ' . ($skipValidation ? 'Yes' : 'No'));
        $this->info("  - Chunk size: $chunkSize");

        if (! $isDryRun && ! $this->confirm('Do you want to proceed with the reassignment?')) {
            $this->info('Operation cancelled by user');

            return 0;
        }

        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        $errors = [];
        $warnings = [];

        $chunks = array_chunk($csvData, $chunkSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            foreach ($chunk as $index => $row) {
                $globalIndex = ($chunkIndex * $chunkSize) + $index + 1;

                $result = $this->processMediaReassignment(
                    $row,
                    $globalIndex,
                    $isDryRun,
                    $skipValidation
                );

                if ($result['success']) {
                    $successCount++;
                } elseif ($result['warning']) {
                    $warningCount++;
                    $warnings[] = $result['message'];
                } else {
                    $errorCount++;
                    $errors[] = $result['error'];
                }

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Processing complete!');
        $this->info("Total records: $totalRows");
        $this->info('Successful ' . ($isDryRun ? 'previews' : 'updates') . ": $successCount");
        $this->info("Warnings: $warningCount");
        $this->info("Errors: $errorCount");

        if (! empty($warnings)) {
            $this->warn("\nWarnings encountered:");
            foreach ($warnings as $warning) {
                $this->warn($warning);
            }
        }

        if (! empty($errors)) {
            $this->error("\nErrors encountered:");
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        return $errorCount > 0 ? 1 : 0;
    }

    private function validateCsvStructure(array $csvData, array $requiredColumns): array
    {
        if (empty($csvData)) {
            return ['valid' => false, 'error' => 'CSV file is empty'];
        }

        $firstRow = $csvData[0];
        $missingColumns = [];

        foreach ($requiredColumns as $column) {
            if (! array_key_exists($column, $firstRow)) {
                $missingColumns[] = $column;
            }
        }

        if (! empty($missingColumns)) {
            return [
                'valid' => false,
                'error' => 'Missing required columns: ' . implode(', ', $missingColumns),
            ];
        }

        return ['valid' => true];
    }

    private function readCsvFile(string $path): array
    {
        $header = null;
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (! $header) {
                    $header = $data;
                } else {
                    $rows[] = array_combine($header, $data);
                }
            }
            fclose($handle);
        }

        return $rows;
    }

    private function processMediaReassignment(
        array $row,
        int $rowNumber,
        bool $isDryRun = false,
        bool $skipValidation = false
    ): array {
        try {
            if (empty($row['uuid']) || empty($row['new_collection_name'])) {
                return [
                    'success' => false,
                    'warning' => false,
                    'error' => "Row $rowNumber: Missing required fields (uuid or new_collection_name)",
                ];
            }

            $media = Media::where('uuid', $row['uuid'])->first();

            if (! $media) {
                return [
                    'success' => false,
                    'warning' => false,
                    'error' => "Row $rowNumber: Media not found for UUID: {$row['uuid']}",
                ];
            }

            if (! $skipValidation && ! empty($row['model_type']) && $media->model_type !== $row['model_type']) {
                return [
                    'success' => false,
                    'warning' => false,
                    'error' => "Row $rowNumber: Model type mismatch for UUID {$row['uuid']}. Expected: {$row['model_type']}, Found: {$media->model_type}",
                ];
            }

            if (! $skipValidation && ! empty($row['file_name']) && $media->file_name !== $row['file_name']) {
                return [
                    'success' => false,
                    'warning' => false,
                    'error' => "Row $rowNumber: File name mismatch for UUID {$row['uuid']}. Expected: {$row['file_name']}, Found: {$media->file_name}",
                ];
            }

            $newCollection = $row['new_collection_name'];
            if (! in_array($newCollection, $this->validCollections)) {
                return [
                    'success' => false,
                    'warning' => true,
                    'message' => "Row $rowNumber: Warning - Collection '$newCollection' is not in the list of valid collections",
                ];
            }

            $originalCollection = $media->collection_name;

            if ($originalCollection === $newCollection) {
                return [
                    'success' => false,
                    'warning' => true,
                    'message' => "Row $rowNumber: Media UUID {$row['uuid']} already in collection '$newCollection'",
                ];
            }

            if (! $isDryRun) {
                $media->collection_name = $newCollection;
                $media->save();
            }

            $action = $isDryRun ? 'Would update' : 'Successfully updated';
            $this->line("\nRow $rowNumber: $action media UUID {$row['uuid']}");
            $this->line("  File: {$media->file_name}");
            $this->line("  Collection: $originalCollection → $newCollection");

            return ['success' => true, 'warning' => false];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'warning' => false,
                'error' => "Row $rowNumber: Exception occurred - " . $e->getMessage(),
            ];
        }
    }
}

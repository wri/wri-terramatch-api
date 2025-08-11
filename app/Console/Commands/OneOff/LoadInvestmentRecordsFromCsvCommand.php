<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Investments\Investment;
use App\Models\V2\Investments\InvestmentSplit;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoadInvestmentRecordsFromCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:load-investment-records-from-csv 
                            {investments_file : Full path to the investments CSV file} 
                            {splits_file : Full path to the investment splits CSV file}
                            {--dry-run : Preview the import without persisting data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load investment records and investment splits from CSV files, using Investments as guide for splits';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $investmentsFile = $this->argument('investments_file');
        $splitsFile = $this->argument('splits_file');
        $isDryRun = $this->option('dry-run');

        if (!file_exists($investmentsFile)) {
            $this->error("Investments file not found: {$investmentsFile}");
            return 1;
        }

        if (!file_exists($splitsFile)) {
            $this->error("Splits file not found: {$splitsFile}");
            return 1;
        }

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No data will be persisted to the database');
        }

        try {
            if (!$isDryRun) {
                DB::beginTransaction();
            }

            $this->info('Starting to load investment records from CSV files...');

            // Process investments first
            $investmentUuidMapping = $this->processInvestmentsFile($investmentsFile, $isDryRun);
            
            // Then process investment splits using the investments as guide
            $this->processInvestmentSplitsFile($splitsFile, $investmentUuidMapping, $isDryRun);

            if (!$isDryRun) {
                DB::commit();
                $this->info('All investment records loaded successfully!');
            } else {
                $this->info('Dry run completed successfully. No data was persisted.');
            }

            return 0;

        } catch (\Exception $e) {
            if (!$isDryRun) {
                DB::rollBack();
            }
            $this->error('Failed to load investment records: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Process the investments CSV file
     */
    protected function processInvestmentsFile(string $filename, bool $isDryRun): array
    {
        $this->info("Processing investments from: {$filename}");

        $handle = fopen($filename, 'r');
        if (!$handle) {
            throw new \Exception("Unable to open file: {$filename}");
        }

        $header = fgetcsv($handle, 1000, ';'); // Using semicolon as delimiter
        if (!$header) {
            fclose($handle);
            throw new \Exception("Unable to read header from file: {$filename}");
        }

        $this->info("Headers found: " . implode(', ', $header));

        $uuidMapping = [];
        $processedCount = 0;
        $skippedCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $rowNumber++;

            if (empty(array_filter($row))) {
                continue;
            }

            $record = array_combine($header, $row);
            if (!$record) {
                $this->warn("Skipping malformed row {$rowNumber}");
                $skippedCount++;
                continue;
            }

            // Clean column names to handle BOM and encoding issues
            $cleanedRecord = [];
            foreach ($record as $key => $value) {
                $cleanedKey = trim($key, "\xEF\xBB\xBF"); // Remove BOM
                $cleanedRecord[$cleanedKey] = trim($value);
            }

            // Validate required fields
            if (empty($cleanedRecord['UUID']) || empty($cleanedRecord['projectUuid']) || 
                empty($cleanedRecord['investmentDate']) || empty($cleanedRecord['type'])) {
                $this->warn("Row {$rowNumber}: Missing required fields. Skipping.");
                $this->warn("Available fields: " . json_encode($cleanedRecord));
                $skippedCount++;
                continue;
            }

            // Validate project exists
            $project = Project::find($cleanedRecord['projectUuid']);
            if (!$project) {
                $this->warn("Row {$rowNumber}: Project ID {$cleanedRecord['projectUuid']} not found. Skipping.");
                $skippedCount++;
                continue;
            }

            // Parse and validate date
            $investmentDate = $this->parseDate($cleanedRecord['investmentDate']);
            if (!$investmentDate) {
                $this->warn("Row {$rowNumber}: Invalid date format '{$cleanedRecord['investmentDate']}'. Skipping.");
                $skippedCount++;
                continue;
            }

            if (!$isDryRun) {
                $investment = Investment::create([
                    'uuid' => $cleanedRecord['UUID'],
                    'project_id' => $cleanedRecord['projectUuid'],
                    'investment_date' => $investmentDate,
                    'type' => $cleanedRecord['type'],
                ]);
                
                $uuidMapping[$cleanedRecord['UUID']] = $investment->id;
                $this->info("Created investment with UUID: {$investment->uuid} for project {$cleanedRecord['projectUuid']}");
            } else {
                $uuidMapping[$cleanedRecord['UUID']] = $cleanedRecord['UUID'];
                $this->line("Would create investment: UUID={$cleanedRecord['UUID']}, Project={$cleanedRecord['projectUuid']}, Date={$investmentDate}, Type={$cleanedRecord['type']}");
            }

            $processedCount++;
        }

        fclose($handle);
        $this->info("Investments processed: {$processedCount}, skipped: {$skippedCount}");

        return $uuidMapping;
    }

    /**
     * Process the investment splits CSV file
     */
    protected function processInvestmentSplitsFile(string $filename, array $investmentUuidMapping, bool $isDryRun): void
    {
        $this->info("Processing investment splits from: {$filename}");

        $handle = fopen($filename, 'r');
        if (!$handle) {
            throw new \Exception("Unable to open file: {$filename}");
        }

        $header = fgetcsv($handle, 1000, ';'); // Using semicolon as delimiter
        if (!$header) {
            fclose($handle);
            throw new \Exception("Unable to read header from file: {$filename}");
        }

        $this->info("Headers found: " . implode(', ', $header));

        $processedCount = 0;
        $skippedCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $rowNumber++;

            if (empty(array_filter($row))) {
                continue;
            }

            $record = array_combine($header, $row);
            if (!$record) {
                $this->warn("Skipping malformed row {$rowNumber}");
                $skippedCount++;
                continue;
            }

            // Clean column names
            $cleanedRecord = [];
            foreach ($record as $key => $value) {
                $cleanedKey = trim($key, "\xEF\xBB\xBF"); // Remove BOM
                $cleanedRecord[$cleanedKey] = trim($value);
            }

            // Validate required fields
            if (empty($cleanedRecord['uuid']) || empty($cleanedRecord['funder']) || !isset($cleanedRecord['amount'])) {
                $this->warn("Row {$rowNumber}: Missing required fields. Skipping.");
                $this->warn("Available fields: " . json_encode($cleanedRecord));
                $skippedCount++;
                continue;
            }

            // The uuid in splits file corresponds to the investment UUID
            $investmentUuid = $cleanedRecord['uuid'];
            
            if (!isset($investmentUuidMapping[$investmentUuid])) {
                $this->warn("Row {$rowNumber}: Investment UUID {$investmentUuid} not found in investments file. Skipping.");
                $skippedCount++;
                continue;
            }

            // Parse and validate amount
            $amount = $this->parseAmount($cleanedRecord['amount']);
            if ($amount === null) {
                $this->warn("Row {$rowNumber}: Invalid amount format '{$cleanedRecord['amount']}'. Skipping.");
                $skippedCount++;
                continue;
            }

            if (!$isDryRun) {
                $investmentId = $investmentUuidMapping[$investmentUuid];
                $investment = Investment::find($investmentId);

                if (!$investment) {
                    $this->warn("Row {$rowNumber}: Investment with ID {$investmentId} not found. Skipping.");
                    $skippedCount++;
                    continue;
                }

                $investmentSplit = InvestmentSplit::create([
                    'uuid' => Str::uuid()->toString(), // Generate new UUID for the split
                    'investment_id' => $investment->id,
                    'funder' => $cleanedRecord['funder'],
                    'amount' => $amount,
                ]);

                $this->info("Created investment split with UUID: {$investmentSplit->uuid} for funder: {$cleanedRecord['funder']}");
            } else {
                $this->line("Would create investment split: Investment UUID={$investmentUuid}, Funder={$cleanedRecord['funder']}, Amount={$amount}");
            }

            $processedCount++;
        }

        fclose($handle);
        $this->info("Investment splits processed: {$processedCount}, skipped: {$skippedCount}");
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate(string $dateString): ?string
    {
        // Try different date formats
        $formats = [
            'n/j/Y',    // 1/4/2025
            'm/d/Y',    // 01/04/2025
            'Y-m-d',    // 2025-01-04
            'd/m/Y',    // 04/01/2025
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Try strtotime as fallback
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Parse amount from various formats
     */
    protected function parseAmount(string $amountString): ?float
    {
        // Remove currency symbols and commas
        $cleanAmount = preg_replace('/[$,]/', '', $amountString);
        
        // Try to convert to float
        if (is_numeric($cleanAmount)) {
            return (float) $cleanAmount;
        }

        return null;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\V2\Investments\Investment;
use App\Models\V2\Investments\InvestmentSplit;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadInvestmentCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:investment-csv 
                            {--investments-file=imports/Investments.csv : Path to the investments CSV file}
                            {--splits-file=imports/Investment splits.csv : Path to the investment splits CSV file}
                            {--dry-run : Preview the import without persisting data}
                            {--force : Force import even if files don\'t exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload investment and investment split CSV files from the imports directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $investmentsFile = $this->option('investments-file');
        $splitsFile = $this->option('splits-file');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🚀 Starting CSV investment files import process...');
        $this->info("📁 Investments file: {$investmentsFile}");
        $this->info("📁 Splits file: {$splitsFile}");

        // Check file existence
        if (!file_exists($investmentsFile) && !$force) {
            $this->error("❌ Investments file not found: {$investmentsFile}");
            $this->warn("💡 Use --force to continue or specify a different path with --investments-file");
            return 1;
        }

        if (!file_exists($splitsFile) && !$force) {
            $this->error("❌ Splits file not found: {$splitsFile}");
            $this->warn("💡 Use --force to continue or specify a different path with --splits-file");
            return 1;
        }

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE: No data will be persisted to the database');
        }

        try {
            if (!$isDryRun) {
                DB::beginTransaction();
                $this->info('💾 Starting database transaction...');
            }

            // Process investments file
            $investmentUuidMapping = [];
            if (file_exists($investmentsFile)) {
                $investmentUuidMapping = $this->importInvestments($investmentsFile, $isDryRun);
            } else {
                $this->warn("⚠️ Investments file not found, continuing with splits only...");
            }
            
            // Process splits file
            if (file_exists($splitsFile)) {
                $this->importInvestmentSplits($splitsFile, $investmentUuidMapping, $isDryRun);
            } else {
                $this->warn("⚠️ Splits file not found, continuing with investments only...");
            }

            if (!$isDryRun) {
                DB::commit();
                $this->info('✅ Import completed successfully!');
            } else {
                $this->info('🔍 Dry run completed successfully. No data was persisted.');
            }

            return 0;

        } catch (\Exception $e) {
            if (!$isDryRun) {
                DB::rollBack();
                $this->error('💥 Transaction error, rolling back...');
            }
            $this->error('❌ Error during import: ' . $e->getMessage());
            $this->error('📋 Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Import investments from CSV file
     *
     * @param string $filename
     * @param bool $isDryRun
     * @return array Mapping of CSV UUID to database ID (or UUID for dry-run)
     */
    protected function importInvestments(string $filename, bool $isDryRun): array
    {
        $this->info("📊 Processing investments from: {$filename}");

        $handle = fopen($filename, 'r');

        if (! $handle) {
            throw new \Exception("Unable to open file: {$filename}");
        }

        // Use semicolon as delimiter for imports files
        $header = fgetcsv($handle, 1000, ';');
        if (! $header) {
            fclose($handle);
            throw new \Exception("Unable to read header from file: {$filename}");
        }

        $this->info("📋 Headers found: " . implode(', ', $header));

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
            if (! $record) {
                $this->warn("⚠️ Row {$rowNumber} malformed, skipping...");
                $skippedCount++;
                continue;
            }

            // Clean column names to handle BOM and encoding issues
            $cleanedRecord = [];
            foreach ($record as $key => $value) {
                $cleanedKey = trim($key, "\xEF\xBB\xBF"); // Remove BOM
                $cleanedRecord[$cleanedKey] = trim($value);
            }

            // Validate required fields according to Investments.csv structure
            if (empty($cleanedRecord['projectUuid']) || 
                empty($cleanedRecord['investmentDate']) || empty($cleanedRecord['type'])) {
                $this->warn("⚠️ Row {$rowNumber}: Missing required fields. Skipping...");
                $this->warn("📋 Available fields: " . json_encode($cleanedRecord));
                $skippedCount++;
                continue;
            }

            // Validate that project exists
            $project = Project::find($cleanedRecord['projectUuid']);
            if (! $project) {
                $this->warn("⚠️ Row {$rowNumber}: Project ID {$cleanedRecord['projectUuid']} not found. Skipping...");
                $skippedCount++;
                continue;
            }

            // Parse and validate date
            $investmentDate = $this->parseDate($cleanedRecord['investmentDate']);
            if (!$investmentDate) {
                $this->warn("⚠️ Row {$rowNumber}: Invalid date format '{$cleanedRecord['investmentDate']}'. Skipping...");
                $skippedCount++;
                continue;
            }

            // Generate UUID if it doesn't exist
            $investmentUuid = !empty($cleanedRecord['UUID']) ? $cleanedRecord['UUID'] : Str::uuid()->toString();
            
            if (! $isDryRun) {
                $investment = Investment::create([
                    'uuid' => $investmentUuid,
                    'project_id' => $cleanedRecord['projectUuid'],
                    'investment_date' => $investmentDate,
                    'type' => $cleanedRecord['type'],
                ]);
                $uuidMapping[$cleanedRecord['projectUuid']] = $investment->id; // Use projectUuid as key
                $this->info("✅ Investment created with UUID: {$investment->uuid} for project {$cleanedRecord['projectUuid']}");
            } else {
                $uuidMapping[$cleanedRecord['projectUuid']] = $investmentUuid; // Use projectUuid as key
                $this->line("🔍 Would create investment: UUID={$investmentUuid}, Project={$cleanedRecord['projectUuid']}, Date={$investmentDate}, Type={$cleanedRecord['type']}");
            }

            $processedCount++;
        }

        fclose($handle);
        $this->info("📊 Investments processed: {$processedCount}, skipped: {$skippedCount}");

        return $uuidMapping;
    }

    /**
     * Import investment splits from CSV file
     *
     * @param string $filename
     * @param array $investmentUuidMapping
     * @param bool $isDryRun
     */
    protected function importInvestmentSplits(string $filename, array $investmentUuidMapping, bool $isDryRun): void
    {
        $this->info("📊 Processing investment splits from: {$filename}");

        $handle = fopen($filename, 'r');

        if (! $handle) {
            throw new \Exception("Unable to open file: {$filename}");
        }

        // Use semicolon as delimiter for imports files
        $header = fgetcsv($handle, 1000, ';');
        if (! $header) {
            fclose($handle);
            throw new \Exception("Unable to read header from file: {$filename}");
        }

        $this->info("📋 Headers found: " . implode(', ', $header));

        $processedCount = 0;
        $skippedCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $rowNumber++;

            if (empty(array_filter($row))) {
                continue;
            }

            $record = array_combine($header, $row);
            if (! $record) {
                $this->warn("⚠️ Row {$rowNumber} malformed, skipping...");
                $skippedCount++;
                continue;
            }

            // Clean column names
            $cleanedRecord = [];
            foreach ($record as $key => $value) {
                $cleanedKey = trim($key, "\xEF\xBB\xBF"); // Remove BOM
                $cleanedRecord[$cleanedKey] = trim($value);
            }

            // Validate required fields according to Investment splits.csv structure
            if (empty($cleanedRecord['investmentUuid']) || empty($cleanedRecord['funder']) || !isset($cleanedRecord['amount'])) {
                $this->warn("⚠️ Row {$rowNumber}: Missing required fields. Skipping...");
                $this->warn("📋 Available fields: " . json_encode($cleanedRecord));
                $skippedCount++;
                continue;
            }

            // The investmentUuid in the splits file corresponds to the projectUuid of the investment
            $projectUuid = $cleanedRecord['investmentUuid'];
            
            if (!isset($investmentUuidMapping[$projectUuid])) {
                $this->warn("⚠️ Row {$rowNumber}: ProjectUuid {$projectUuid} not found in investments file. Skipping...");
                $skippedCount++;
                continue;
            }

            // Parse and validate amount
            $amount = $this->parseAmount($cleanedRecord['amount']);
            if ($amount === null) {
                $this->warn("⚠️ Row {$rowNumber}: Invalid amount format '{$cleanedRecord['amount']}'. Skipping...");
                $skippedCount++;
                continue;
            }

            if (! $isDryRun) {
                $investmentId = $investmentUuidMapping[$projectUuid];
                $investment = Investment::find($investmentId);

                if (! $investment) {
                    $this->warn("⚠️ Row {$rowNumber}: Investment with ID {$investmentId} not found. Skipping...");
                    $skippedCount++;
                    continue;
                }

                // Generate UUID for split if it doesn't exist
                $splitUuid = !empty($cleanedRecord['uuid']) ? $cleanedRecord['uuid'] : Str::uuid()->toString();

                $investmentSplit = InvestmentSplit::create([
                    'uuid' => $splitUuid,
                    'investment_id' => $investment->id,
                    'funder' => $cleanedRecord['funder'],
                    'amount' => $amount,
                ]);

                $this->info("✅ Investment split created with UUID: {$investmentSplit->uuid} for funder: {$cleanedRecord['funder']}");
            } else {
                $this->line("🔍 Would create investment split: ProjectUuid={$projectUuid}, Funder={$cleanedRecord['funder']}, Amount={$amount}");
            }

            $processedCount++;
        }

        fclose($handle);
        $this->info("📊 Investment splits processed: {$processedCount}, skipped: {$skippedCount}");
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

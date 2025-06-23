<?php

namespace App\Console\Commands;

use App\Models\V2\Investments\Investment;
use App\Models\V2\Investments\InvestmentSplit;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportInvestmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:investments 
                            {investments_file : Full path to the investments CSV file} 
                            {splits_file : Full path to the investment splits CSV file}
                            {--dry-run : Preview the import without persisting data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import investments and investment splits from CSV files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $investmentsFile = $this->argument('investments_file');
        $splitsFile = $this->argument('splits_file');
        $isDryRun = $this->option('dry-run');

        if (! file_exists($investmentsFile)) {
            $this->error("Investments file not found: {$investmentsFile}");

            return 1;
        }

        if (! file_exists($splitsFile)) {
            $this->error("Splits file not found: {$splitsFile}");

            return 1;
        }

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No data will be persisted to the database');
        }

        try {
            DB::beginTransaction();

            $investmentIdMapping = $this->importInvestments($investmentsFile, $isDryRun);

            $this->importInvestmentSplits($splitsFile, $investmentIdMapping, $isDryRun);

            if ($isDryRun) {
                DB::rollBack();
                $this->info('Dry run completed successfully. No data was persisted.');
            } else {
                DB::commit();
                $this->info('Import completed successfully!');
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Import investments from CSV file
     *
     * @param string $filename
     * @param bool $isDryRun
     * @return array Mapping of CSV id to database ID (or UUID for dry-run)
     */
    protected function importInvestments(string $filename, bool $isDryRun): array
    {
        $this->info("Processing investments from: {$filename}");

        $handle = fopen($filename, 'r');

        if (! $handle) {
            throw new \Exception("Unable to open file: {$filename}");
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            throw new \Exception("Unable to read header from file: {$filename}");
        }

        $idMapping = [];
        $processedCount = 0;
        $skippedCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (empty(array_filter($row))) {
                continue;
            }

            $record = array_combine($header, $row);
            if (! $record) {
                $this->warn("Skipping malformed row {$rowNumber}");
                $skippedCount++;

                continue;
            }

            if (empty($record['id']) || empty($record['project_id']) || empty($record['investment_date']) || empty($record['type'])) {
                $this->warn("Skipping row {$rowNumber}: Missing required fields");
                $skippedCount++;

                continue;
            }

            $project = Project::find($record['project_id']);
            if (! $project) {
                $this->warn("Skipping row {$rowNumber}: Project ID {$record['project_id']} not found");
                $skippedCount++;

                continue;
            }

            $uuid = Str::uuid()->toString();

            if (! $isDryRun) {
                $investment = Investment::create([
                    'uuid' => $uuid,
                    'project_id' => $record['project_id'],
                    'investment_date' => $record['investment_date'],
                    'type' => $record['type'],
                ]);
                $idMapping[$record['id']] = $investment->id;
            } else {
                $idMapping[$record['id']] = $uuid;
            }

            $processedCount++;

            if ($isDryRun) {
                $this->line("Would create investment: ID={$record['id']}, UUID={$uuid}, Project={$record['project_id']}, Date={$record['investment_date']}, Type={$record['type']}");
            }
        }

        fclose($handle);
        $this->info("Investments processed: {$processedCount}, skipped: {$skippedCount}");

        return $idMapping;
    }

    /**
     * Import investment splits from CSV file
     *
     * @param string $filename
     * @param array $investmentIdMapping
     * @param bool $isDryRun
     */
    protected function importInvestmentSplits(string $filename, array $investmentIdMapping, bool $isDryRun): void
    {
        $this->info("Processing investment splits from: {$filename}");

        $handle = fopen($filename, 'r');

        if (! $handle) {
            throw new \Exception("Unable to open file: {$filename}");
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            throw new \Exception("Unable to read header from file: {$filename}");
        }

        $processedCount = 0;
        $skippedCount = 0;
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (empty(array_filter($row))) {
                continue;
            }

            $record = array_combine($header, $row);
            if (! $record) {
                $this->warn("Skipping malformed row {$rowNumber}");
                $skippedCount++;

                continue;
            }

            if (empty($record['investment_id']) || empty($record['funder']) || ! isset($record['amount'])) {
                $this->warn("Skipping row {$rowNumber}: Missing required fields");
                $skippedCount++;

                continue;
            }

            if (! isset($investmentIdMapping[$record['investment_id']])) {
                $this->warn("Skipping row {$rowNumber}: Investment ID {$record['investment_id']} not found in investments file");
                $skippedCount++;

                continue;
            }

            $investmentRef = $investmentIdMapping[$record['investment_id']];

            if (! $isDryRun) {
                $investmentId = $investmentRef;
                $investment = Investment::find($investmentId);

                if (! $investment) {
                    $this->warn("Skipping row {$rowNumber}: Investment with ID {$investmentId} not found");
                    $skippedCount++;

                    continue;
                }
            } else {
                $investment = null;
            }

            $splitUuid = Str::uuid()->toString();

            if (! $isDryRun) {
                InvestmentSplit::create([
                    'uuid' => $splitUuid,
                    'investment_id' => $investment->id,
                    'funder' => $record['funder'],
                    'amount' => $record['amount'],
                ]);
            }

            $processedCount++;

            if ($isDryRun) {
                $this->line("Would create investment split: UUID={$splitUuid}, Investment={$record['investment_id']}, Funder={$record['funder']}, Amount={$record['amount']}");
            }
        }

        fclose($handle);
        $this->info("Investment splits processed: {$processedCount}, skipped: {$skippedCount}");
    }
}

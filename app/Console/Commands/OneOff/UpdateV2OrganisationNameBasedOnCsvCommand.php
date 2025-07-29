<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Organisation;
use Illuminate\Console\Command;

class UpdateV2OrganisationNameBasedOnCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-v2-organisation-name-based-on-csv-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update V2 Organisation Name Based On Csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path('imports/organisations_cleaned.csv'); // Adjust path as needed

        if (! file_exists($path) || ! is_readable($path)) {
            $this->error("CSV file not found or not readable at: $path");

            return 1;
        }

        $header = null;
        $rows = [];

        // Open and read the CSV
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (! $header) {
                    $header = $data; // First row is the header
                } else {
                    $rows[] = array_combine($header, $data);
                }
            }
            fclose($handle);
        }

        $updatedCount = 0;
        $notFoundCount = 0;
        $errorCount = 0;

        // Process each row
        foreach ($rows as $index => $row) {
            $this->info("Processing row #$index: " . json_encode($row));

            // Clean column names to handle BOM and encoding issues
            $cleanedRow = [];
            foreach ($row as $key => $value) {
                $cleanedKey = trim($key, "\xEF\xBB\xBF"); // Remove BOM
                $cleanedRow[$cleanedKey] = $value;
            }

            // Validate required columns exist
            if (! isset($cleanedRow['uuid']) || ! isset($cleanedRow['name'])) {
                $this->error("Row #$index: Missing required columns 'uuid' or 'name'");
                $this->error('Available columns: ' . implode(', ', array_keys($cleanedRow)));
                $errorCount++;

                continue;
            }

            $organisation = Organisation::isUuid($cleanedRow['uuid'])->first();

            if (! $organisation) {
                $this->error("Row #$index: Organisation not found for UUID: " . $cleanedRow['uuid']);
                $notFoundCount++;

                continue;
            }

            try {
                $oldName = $organisation->name;
                $organisation->name = trim($cleanedRow['name']);
                $organisation->save();

                $this->info("Row #$index: Updated organisation '{$oldName}' to '{$organisation->name}'");
                $updatedCount++;
            } catch (\Exception $e) {
                $this->error("Row #$index: Error updating organisation name: " . $e->getMessage());
                $errorCount++;

                continue;
            }
        }

        $this->info('CSV processing complete.');
        $this->info('Total rows processed: ' . count($rows));
        $this->info("Successfully updated: $updatedCount");
        $this->info("Organisations not found: $notFoundCount");
        $this->info("Errors encountered: $errorCount");

        return 0;
    }
}

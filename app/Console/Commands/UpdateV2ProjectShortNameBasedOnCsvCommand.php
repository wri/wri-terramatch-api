<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;

class UpdateV2ProjectShortNameBasedOnCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-v2-project-short-name-based-on-csv-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update V2 Project Short Name Based On Csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path('imports/tf_project_codes.csv'); // Adjust path as needed

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

        // Process each row
        foreach ($rows as $index => $row) {
            $this->info("Row #$index: " . json_encode($row));

            $project = Project::isUuid($row['TM Project UUID'])->first();

            if (! $project) {
                $this->error('Project not found for UUID: ' . $row['TM Project UUID']);

                continue;
            }

            try {
                $project->short_name = strtoupper($row['Project Code']);
                $project->save();
            } catch (\Exception $e) {
                $this->error('Error updating project short name: ' . $e->getMessage());

                continue;
            }

            $organisation = $project->organisation;

            if (! $organisation) {
                $this->error('Organisation not found for project: ' . $project->id);

                continue;
            }

            if ($organisation->name !== $row['Organization Name (from Full RFP Vetting) (from Project Code)']) {
                $this->error('Organisation name mismatch for project: ' . $project->id);
                $this->error('Expected: |' . $row['Organization Name (from Full RFP Vetting) (from Project Code)'] . '|');
                $this->error('Actual: |' . $organisation->name . '|');
            }
        }

        $this->info('CSV processing complete. Rows processed: ' . count($rows));

        return 0;
    }
}

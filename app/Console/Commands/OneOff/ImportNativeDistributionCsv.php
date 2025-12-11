<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\TreeSpecies\TreeSpeciesResearch;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportNativeDistributionCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:import-native-distribution {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import native distribution data from CSV to tree_species_research entity';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filename = $this->argument('filename');
        $filePath = base_path('imports/') . $filename;

        if (! file_exists($filePath)) {
            $this->error('File not found: ' . $filePath);

            return 1;
        }

        $this->info('Reading CSV file: ' . $filename);

        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($records as $index => $record) {
            try {
                // Get taxon_id from the record
                $taxonId = $record['taxon_id'] ?? null;

                if (! $taxonId) {
                    $this->warn('Skipping row ' . ($index + 2) . ' - missing taxon_id');
                    $skipped++;

                    continue;
                }

                // Find the tree species research record
                $treeSpecies = TreeSpeciesResearch::where('taxon_id', $taxonId)->first();

                if (! $treeSpecies) {
                    $this->warn('Skipping row ' . ($index + 2) . ' - taxon_id \'' . $taxonId . '\' not found in tree_species_research');
                    $skipped++;

                    continue;
                }

                // Get native_distribution from CSV (could be in different column names)
                $nativeDistribution = null;
                if (isset($record['native_distribution'])) {
                    $nativeDistribution = $record['native_distribution'];
                } elseif (isset($record['native distribution'])) {
                    $nativeDistribution = $record['native distribution'];
                } elseif (isset($record['distribution'])) {
                    $nativeDistribution = $record['distribution'];
                }

                if (! $nativeDistribution || trim($nativeDistribution) === '') {
                    $this->warn('Skipping row ' . ($index + 2) . ' - taxon_id \'' . $taxonId . '\' has no native_distribution data');
                    $skipped++;

                    continue;
                }

                // Parse the native distribution string into an array
                // Handle various formats: comma-separated, semicolon-separated, space-separated
                $distributionArray = $this->parseDistributionString($nativeDistribution);

                if (empty($distributionArray)) {
                    $this->warn('Skipping row ' . ($index + 2) . ' - taxon_id \'' . $taxonId . '\' could not parse native_distribution: \'' . $nativeDistribution . '\'');
                    $skipped++;

                    continue;
                }

                // Update the native_distribution field
                $treeSpecies->native_distribution = $distributionArray;
                $treeSpecies->save();

                $updated++;
                $this->info('Updated taxon_id \'' . $taxonId . '\' with native_distribution: ' . json_encode($distributionArray));

            } catch (\Exception $e) {
                $errors++;
                $this->error('Error processing row ' . ($index + 2) . ': ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('Import completed!');
        $this->info('Updated: ' . $updated);
        $this->info('Skipped: ' . $skipped);
        $this->info('Errors: ' . $errors);

        return 0;
    }

    /**
     * Parse distribution string into an array.
     * Handles various formats: comma-separated, semicolon-separated, space-separated, or JSON array.
     *
     * @param string $distributionString
     * @return array
     */
    private function parseDistributionString(string $distributionString): array
    {
        $distributionString = trim($distributionString);

        // If it's already a JSON array, decode it
        if (strpos($distributionString, '[') === 0 && strpos($distributionString, ']') === strlen($distributionString) - 1) {
            $decoded = json_decode($distributionString, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_map('trim', $decoded);
            }
        }

        // Try comma-separated first (most common)
        if (strpos($distributionString, ',') !== false) {
            $items = explode(',', $distributionString);

            return array_map('trim', array_filter($items, fn ($item) => ! empty(trim($item))));
        }

        // Try semicolon-separated
        if (strpos($distributionString, ';') !== false) {
            $items = explode(';', $distributionString);

            return array_map('trim', array_filter($items, fn ($item) => ! empty(trim($item))));
        }

        // Try pipe-separated
        if (strpos($distributionString, '|') !== false) {
            $items = explode('|', $distributionString);

            return array_map('trim', array_filter($items, fn ($item) => ! empty(trim($item))));
        }

        // If no delimiter found, treat the whole string as a single value
        return [$distributionString];
    }
}

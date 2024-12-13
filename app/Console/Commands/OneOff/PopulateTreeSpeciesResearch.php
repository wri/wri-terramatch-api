<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Console\Commands\Traits\ExceptionLevel;
use App\Models\V2\TreeSpecies\TreeSpeciesResearch;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PopulateTreeSpeciesResearch extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:populate-tree-species-research {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Given an input file, populates the tree_species_research table';

    // The names of the columns we require for inserting into the DB. Key is column name in the header row of the CSV,
    // value is the column name in the DB definition
    protected const COLUMN_MAPPING = [
        'taxonID' => 'taxon_id',
        'scientificName' => 'scientific_name',
        'family' => 'family',
        'genus' => 'genus',
        'specificEpithet' => 'specific_epithet',
        'infraspecificEpithet' => 'infraspecific_epithet',
    ];

    // Populated by parseHeaders(), a mapping of DB colum name to the index in each row where that data is expected to
    // exist
    protected $columns = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->executeAbortableScript(function () {
            $process = new Process(['wc', '-l', $this->argument('file')]);
            $process->run();
            $this->assert($process->isSuccessful(), "WC failed {$process->getErrorOutput()}");

            $lines = ((int)explode(' ', $process->getOutput())[0]) - 1;

            $fileHandle = fopen($this->argument('file'), 'r');
            $this->parseHeaders(fgetcsv($fileHandle));

            $this->withProgressBar($lines, function ($progressBar) use ($fileHandle) {
                $abortExceptions = [];
                while ($csvRow = fgetcsv($fileHandle)) {
                    $data = [];
                    foreach ($this->columns as $column => $index) {
                        $data[$column] = $csvRow[$index];
                    }

                    try {
                        $existing = TreeSpeciesResearch::where('scientific_name', $data['scientific_name'])->first();
                        $this->assert(
                            $existing == null,
                            'Scientific name already exists, skipping: ' . json_encode([
                                'existing_id' => $existing?->taxon_id,
                                'new_id' => $data['taxon_id'],
                                'scientific_name' => $data['scientific_name'],
                            ], JSON_PRETTY_PRINT),
                            ExceptionLevel::Warning
                        );
                        TreeSpeciesResearch::create($data);
                    } catch (AbortException $e) {
                        $abortExceptions[] = $e;
                    }
                    $progressBar->advance();
                }

                $progressBar->finish();

                if (! empty($abortExceptions)) {
                    $this->warn("Errors and warnings encountered during parsing CSV Rows:\n");
                    foreach ($abortExceptions as $error) {
                        $this->logException($error);
                    }
                }
            });

            fclose($fileHandle);
        });
    }

    /**
     * @throws AbortException
     */
    protected function parseHeaders(array $headerRow): void
    {
        foreach ($headerRow as $index => $header) {
            // Excel puts some garbage at the beginning of the file that we need to filter out.
            $header = trim($header, "\xEF\xBB\xBF\"");

            if (array_key_exists($header, self::COLUMN_MAPPING)) {
                $this->columns[self::COLUMN_MAPPING[$header]] = $index;
            }
        }

        $this->assert(
            count(self::COLUMN_MAPPING) === count($this->columns),
            'Not all required columns were found'
        );
    }
}

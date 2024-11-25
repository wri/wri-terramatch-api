<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Models\V2\TreeSpecies\TreeSpeciesResearch;
use Illuminate\Console\Command;

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
            $fileHandle = fopen($this->argument('file'), 'r');
            $this->parseHeaders(fgetcsv($fileHandle, separator: "\t"));

            // The input file at the time of this writing has 1618549 rows of data
            $this->withProgressBar(1618549, function ($progressBar) use ($fileHandle) {
                while ($csvRow = fgetcsv($fileHandle, separator: "\t")) {
                    $data = [];
                    foreach ($this->columns as $column => $index) {
                        $data[$column] = $csvRow[$index];
                    }
                    TreeSpeciesResearch::create($data);
                    $progressBar->advance();
                }

                $progressBar->finish();
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
            $header = trim($header);

            if (array_key_exists($header, self::COLUMN_MAPPING)) {
                $this->columns[self::COLUMN_MAPPING[$header]] = $index;
            }
        }

        $this->assert(
            count(self::COLUMN_MAPPING) === count($this->columns),
            "Not all required columns were found"
        );
    }
}

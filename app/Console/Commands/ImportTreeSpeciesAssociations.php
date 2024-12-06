<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ImportTreeSpeciesAssociations extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-tree-species-associations {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports a CSV that links UUIDs from v2_tree_species to taxon_ids from tree_species_research';

    protected int $treeSpeciesUuidColumn;

    protected int $taxonIdColumn;

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
                while ($csvRow = fgetcsv($fileHandle)) {
                    $treeSpeciesUuid = $csvRow[$this->treeSpeciesUuidColumn];
                    $taxonId = $csvRow[$this->taxonIdColumn];

                    if ($taxonId != 'NA') {
                        TreeSpecies::isUuid($treeSpeciesUuid)->update(['taxon_id' => $taxonId]);
                    }
                    $progressBar->advance();
                }

                $progressBar->finish();
            });

            fclose($fileHandle);
        });
    }

    protected function parseHeaders(array $headerRow): void
    {
        foreach ($headerRow as $index => $header) {
            if ($header == 'tree_species_uuid') {
                $this->treeSpeciesUuidColumn = $index;
            } elseif ($header == 'taxon_id') {
                $this->taxonIdColumn = $index;
            }
        }

        $this->assert(
            $this->treeSpeciesUuidColumn != null && $this->taxonIdColumn != null,
            'Not all required columns were found'
        );
    }
}

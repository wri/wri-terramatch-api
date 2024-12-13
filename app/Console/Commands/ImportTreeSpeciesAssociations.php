<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Console\Commands\Traits\ExceptionLevel;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\TreeSpecies\TreeSpeciesResearch;
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
                $abortExceptions = [];
                while ($csvRow = fgetcsv($fileHandle)) {
                    $treeSpeciesUuid = $csvRow[$this->treeSpeciesUuidColumn];
                    $taxonId = $csvRow[$this->taxonIdColumn];

                    if ($taxonId != 'NA') {
                        try {
                            $research = TreeSpeciesResearch::find($taxonId);
                            $this->assert($research != null, "Taxon ID not found: $taxonId", ExceptionLevel::Warning);

                            TreeSpecies::isUuid($treeSpeciesUuid)->update([
                                'taxon_id' => $taxonId,
                                'name' => $research->name,
                            ]);
                        } catch (AbortException $e) {
                            $abortExceptions[] = $e;
                        }
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

    protected function parseHeaders(array $headerRow): void
    {
        foreach ($headerRow as $index => $header) {
            $header = trim($header, "\xEF\xBB\xBF\"");
            if ($header == 'tree_species_uuid') {
                $this->treeSpeciesUuidColumn = $index;
            } elseif ($header == 'taxon_id') {
                $this->taxonIdColumn = $index;
            }
        }

        $this->assert(
            is_numeric($this->treeSpeciesUuidColumn) && is_numeric($this->taxonIdColumn),
            'Not all required columns were found'
        );
    }
}

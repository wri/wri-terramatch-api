<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Console\Commands\Traits\ExceptionLevel;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RemoveTreeSpeciesSynonyms extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:remove-tree-species-synonyms {file} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Given an input .csv, updates tree species records';

    protected $headerOrder = [];

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
                    $row = [];
                    foreach ($csvRow as $index => $cell) {
                        $field = $this->headerOrder[$index];
                        $row[$field] = $cell;
                    }

                    $this->assert(! empty($row['taxonID']), 'No taxonID found');
                    $this->assert(! empty($row['scientificName']), 'No scientificName found');
                    $this->assert(! empty($row['treeSpeciesUuid']), 'No treeSpeciesUuid found');
                    $this->assert(! empty($row['originalScientificName']), 'No originalScientificName found');

                    try {
                        $treeSpecies = TreeSpecies::isUuid($row['treeSpeciesUuid'])->first();
                        $this->assert($treeSpecies != null, 'Tree species not found: ' . $row['treeSpeciesUuid'], ExceptionLevel::Warning);

                        if (! $this->option('dry-run')) {
                            $treeSpecies->update(['name' => $row['scientificName'], 'taxon_id' => $row['taxonID']]);
                        }
                    } catch (AbortException $e) {
                        $abortExceptions[] = $e;
                    }

                    $progressBar->advance();
                }

                $progressBar->finish();

                if (! empty($abortExceptions)) {
                    $this->warn("\n\nErrors and warnings encountered during parsing CSV Rows:\n");
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
        foreach ($headerRow as $header) {
            // Excel puts some garbage at the beginning of the file that we need to filter out.
            $header = trim($header, "\xEF\xBB\xBF\"");
            $this->headerOrder[] = $header;
        }

        $this->assert(in_array('taxonID', $this->headerOrder), 'No taxonID column found');
        $this->assert(in_array('scientificName', $this->headerOrder), 'No scientificName column found');
        $this->assert(in_array('treeSpeciesUuid', $this->headerOrder), 'No treeSpeciesUuid column found');
        $this->assert(in_array('originalScientificName', $this->headerOrder), 'No originalScientificName column found');
    }
}

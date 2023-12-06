<?php

namespace App\Jobs;

use App\Models\Terrafund\TerrafundCsvImport;
use App\Models\Terrafund\TerrafundTreeSpecies;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTerrafundTreeSpeciesJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $name;

    private $amount;

    private $treeableType;

    private $treeableId;

    private $csvId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $name, int $amount, string $treeableType, int $treeableId, int $csvId)
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->treeableType = $treeableType;
        $this->treeableId = $treeableId;
        $this->csvId = $csvId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $treeSpecies = new TerrafundTreeSpecies();
            $treeSpecies->name = $this->name;
            $treeSpecies->amount = $this->amount;
            $treeSpecies->treeable_type = $this->treeableType;
            $treeSpecies->treeable_id = $this->treeableId;
            $treeSpecies->terrafund_csv_import_id = $this->csvId;
            $treeSpecies->saveOrFail();
        } catch (Exception $exception) {
            $csv = TerrafundCsvImport::where('id', $this->csvId)->firstOrFail();
            $csv->has_failed = true;
            $csv->saveOrFail();
            RemoveTerrafundTreeSpeciesByImportJob::dispatch($this->csvId);
        }
    }
}

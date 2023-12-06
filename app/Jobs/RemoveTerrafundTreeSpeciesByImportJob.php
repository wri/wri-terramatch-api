<?php

namespace App\Jobs;

use App\Models\Terrafund\TerrafundTreeSpecies;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveTerrafundTreeSpeciesByImportJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $csvId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $csvId)
    {
        $this->csvId = $csvId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        TerrafundTreeSpecies::where('terrafund_csv_import_id', $this->csvId)->delete();
    }
}

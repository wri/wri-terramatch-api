<?php

namespace App\Jobs;

use App\Models\SiteTreeSpecies;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveSiteTreeSpeciesByImportJob
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
        SiteTreeSpecies::where('site_csv_import_id', $this->csvId)->delete();
    }
}

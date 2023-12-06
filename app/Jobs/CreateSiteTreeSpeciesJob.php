<?php

namespace App\Jobs;

use App\Models\SiteCsvImport;
use App\Models\SiteTreeSpecies;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSiteTreeSpeciesJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $name;

    private $siteId;

    private $csvId;

    private $amount;

    private $siteSubmissionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $name, int $siteId, int $csvId, int $amount = null, int $siteSubmissionId = null)
    {
        $this->name = $name;
        $this->siteId = $siteId;
        $this->csvId = $csvId;
        $this->amount = $amount;
        $this->siteSubmissionId = $siteSubmissionId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $siteTreeSpecies = new SiteTreeSpecies();
            $siteTreeSpecies->name = $this->name;
            $siteTreeSpecies->amount = $this->amount;
            $siteTreeSpecies->site_id = $this->siteId;
            $siteTreeSpecies->site_csv_import_id = $this->csvId;
            $siteTreeSpecies->site_submission_id = $this->siteSubmissionId;
            $siteTreeSpecies->saveOrFail();
        } catch (Exception $exception) {
            $csv = SiteCsvImport::where('id', $this->csvId)->firstOrFail();
            $csv->has_failed = true;
            $csv->saveOrFail();
            RemoveSiteTreeSpeciesByImportJob::dispatch($this->csvId);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}

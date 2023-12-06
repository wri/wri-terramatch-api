<?php

namespace App\Jobs;

use App\Models\CsvImport;
use App\Models\ProgrammeTreeSpecies;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateProgrammeTreeSpeciesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $name;

    private $programmeId;

    private $csvId;

    private $amount;

    private $programmeSubmissionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $name, int $programmeId, int $csvId, int $amount = null, int $programmeSubmissionId = null)
    {
        $this->name = $name;
        $this->programmeId = $programmeId;
        $this->csvId = $csvId;
        $this->amount = $amount;
        $this->programmeSubmissionId = $programmeSubmissionId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $programmeTreeSpecies = new ProgrammeTreeSpecies();
            $programmeTreeSpecies->name = $this->name;
            $programmeTreeSpecies->amount = $this->amount;
            $programmeTreeSpecies->programme_id = $this->programmeId;
            $programmeTreeSpecies->csv_import_id = $this->csvId;
            $programmeTreeSpecies->programme_submission_id = $this->programmeSubmissionId;
            $programmeTreeSpecies->saveOrFail();
        } catch (Exception $exception) {
            $csv = CsvImport::where('id', $this->csvId)->firstOrFail();
            $csv->status = 'failed';
            $csv->saveOrFail();
            RemoveProgrammeTreeSpeciesByImportJob::dispatch($this->csvId);
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

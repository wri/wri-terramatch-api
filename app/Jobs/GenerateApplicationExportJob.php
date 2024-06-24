<?php

namespace App\Jobs;

use App\Exports\V2\ApplicationExport;
use App\Models\V2\FundingProgramme;
use App\Models\V2\SavedExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class GenerateApplicationExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    private FundingProgramme $fundingProgramme;

    public function __construct(FundingProgramme $fundingProgramme)
    {
        $this->fundingProgramme = $fundingProgramme;
    }

    public function handle()
    {
        ini_set('memory_limit', '-1');

        $name = 'exports/' . $this->fundingProgramme->name . ' Export - ' . now() . '.csv';

        $export = (new ApplicationExport($this->fundingProgramme->applications()->getQuery(), $this->fundingProgramme));

        Excel::store($export, $name, 's3');
        SavedExport::create([
            'name' => $name,
            'funding_programme_id' => $this->fundingProgramme->id,
        ]);
    }
}

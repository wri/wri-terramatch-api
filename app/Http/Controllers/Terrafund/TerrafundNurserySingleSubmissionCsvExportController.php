<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\TerrafundExportHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundNurserySubmission;

class TerrafundNurserySingleSubmissionCsvExportController extends Controller
{
    public function __invoke(TerrafundNurserySubmission $terrafundNurserySubmission)
    {
        $this->authorize('export', TerrafundNurserySubmission::class);

        $csv = TerrafundExportHelper::generateNurserySubmissionCsv(TerrafundNurserySubmission::query()->where('id', $terrafundNurserySubmission));

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Terrafund Nursery Submission Export - ' . now() . '.csv');
    }
}

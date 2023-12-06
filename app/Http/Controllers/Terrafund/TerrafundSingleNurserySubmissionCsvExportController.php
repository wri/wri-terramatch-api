<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\TerrafundExportHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;

class TerrafundSingleNurserySubmissionCsvExportController extends Controller
{
    public function __invoke(TerrafundNursery $terrafundNursery)
    {
        $this->authorize('export', TerrafundNurserySubmission::class);

        $csv = TerrafundExportHelper::generateNurserySubmissionCsv(TerrafundNurserySubmission::query()->where('terrafund_nursery_id', $terrafundNursery->id));

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Terrafund Nursery Submission Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

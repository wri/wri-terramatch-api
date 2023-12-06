<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\TerrafundExportHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundSiteSubmission;

class TerrafundSiteSubmissionCsvExportController extends Controller
{
    public function __invoke()
    {
        $this->authorize('export', TerrafundSiteSubmission::class);

        $csv = TerrafundExportHelper::generateSiteSubmissionCsv(TerrafundSiteSubmission::query());

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Terrafund Site Submission Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\TerrafundExportHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;

class TerrafundSingleSiteSubmissionCsvExportController extends Controller
{
    public function __invoke(TerrafundSite $terrafundSite)
    {
        $this->authorize('exportOwned', TerrafundSiteSubmission::class);

        $csv = TerrafundExportHelper::generateSiteSubmissionCsv(TerrafundSiteSubmission::query()->where('terrafund_site_id', $terrafundSite->id));

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Terrafund Site Submission Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

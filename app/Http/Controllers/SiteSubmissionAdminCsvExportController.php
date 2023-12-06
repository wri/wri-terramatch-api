<?php

namespace App\Http\Controllers;

use App\Helpers\PPCExportHelper;
use App\Models\Site;
use App\Models\SiteSubmission;
use Illuminate\Http\Request;

class SiteSubmissionAdminCsvExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('exportOwned',  Site::class);

        $siteSubmissions = SiteSubmission::query();
        $csv = PPCExportHelper::generateSiteSubmissionCsv($siteSubmissions);

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'PPC Site Submission Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

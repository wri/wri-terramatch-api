<?php

namespace App\Http\Controllers;

use App\Helpers\PPCExportHelper;
use App\Models\Site;
use App\Models\SiteSubmission;

class SiteSubmissionCsvExportController extends Controller
{
    public function __invoke(Site $site)
    {
        $this->authorize('exportOwned',  $site);

        $siteSubmissions = SiteSubmission::where('site_id', $site->id);
        $siteCsv = PPCExportHelper::generateSiteSubmissionCsv($siteSubmissions);

        $filename = public_path('storage/PPC Site Submission Export - ' . now() . '.zip');

        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);
        $zip->addFromString('Site Submissions.csv', $siteCsv->toString());
        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }
}

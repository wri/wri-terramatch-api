<?php

namespace App\Http\Controllers;

use App\Helpers\PPCExportHelper;
use App\Models\Programme;
use App\Models\SiteSubmission;
use App\Models\Submission;

class ProgrammeSubmissionCsvExportController extends Controller
{
    public function __invoke(Programme $programme)
    {
        $this->authorize('exportOwned',  $programme);

        $submissions = Submission::where('programme_id', $programme->id);
        $siteSubmissions = SiteSubmission::whereIn('site_id', $programme->sites->pluck('id'));

        $programmeCsv = PPCExportHelper::generateProgrammeSubmissionCsv($submissions);
        $siteCsv = PPCExportHelper::generateSiteSubmissionCsv($siteSubmissions);

        $filename = public_path('storage/' . stripslashes($programme->name) . ' Programme Submission Export (' . $programme->id . ').zip');

        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);
        $zip->addFromString($programme->name . ' Programme Submissions (' . $programme->id . ').csv', $programmeCsv->toString());
        $zip->addFromString($programme->name . ' Site Submissions (' . $programme->id . ').csv', $siteCsv->toString());
        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }
}

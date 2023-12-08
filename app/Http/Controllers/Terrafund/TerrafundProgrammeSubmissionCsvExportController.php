<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\TerrafundExportHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSiteSubmission;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TerrafundProgrammeSubmissionCsvExportController extends Controller
{
    public function singleProgrammeAction(TerrafundProgramme $terrafundProgramme): BinaryFileResponse
    {
        $this->authorize('exportOwned',  $terrafundProgramme);

        $programmeSubmissions = TerrafundProgrammeSubmission::where('terrafund_programme_id', $terrafundProgramme->id);
        $nurserySubmissions = TerrafundNurserySubmission::whereIn('terrafund_nursery_id', $terrafundProgramme->terrafundNurseries->pluck('id'));
        $siteSubmissions = TerrafundSiteSubmission::whereIn('terrafund_site_id', $terrafundProgramme->terrafundSites->pluck('id'));

        $programmeCsv = TerrafundExportHelper::generateProgrammeSubmissionCsv($programmeSubmissions);
        $siteCsv = TerrafundExportHelper::generateSiteSubmissionCsv($siteSubmissions);
        $nurseryCsv = TerrafundExportHelper::generateNurserySubmissionCsv($nurserySubmissions);

        $filename = public_path('storage/Terrafund Programme Submission Export - ' . now() . '.zip');

        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);
        $zip->addFromString('Programme Submissions.csv', $programmeCsv->toString());
        $zip->addFromString('Site Submissions.csv', $siteCsv->toString());
        $zip->addFromString('Nursery Submissions.csv', $nurseryCsv->toString());
        $zip->close();

        return response()->download($filename)->deleteFileAfterSend();
    }

    public function allProgrammesAction()
    {
        $this->authorize('export', TerrafundProgramme::class);

        $csv = TerrafundExportHelper::generateProgrammeSubmissionCsv(TerrafundProgrammeSubmission::query());

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Terrafund Programme Submission Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\TerrafundExportHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundNurserySubmission;

class TerrafundNurserySubmissionCsvExportController extends Controller
{
    public function __invoke()
    {
        $this->authorize('exportOwned', TerrafundNurserySubmission::class);

        $csv = TerrafundExportHelper::generateNurserySubmissionCsv(TerrafundNurserySubmission::query());

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Terrafund Nursery Submission Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

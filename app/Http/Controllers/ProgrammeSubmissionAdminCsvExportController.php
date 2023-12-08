<?php

namespace App\Http\Controllers;

use App\Helpers\PPCExportHelper;
use App\Models\Programme;
use App\Models\Submission;
use Illuminate\Http\Request;

class ProgrammeSubmissionAdminCsvExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('export',  Programme::class);

        $submissions = Submission::query();

        $csv = PPCExportHelper::generateProgrammeSubmissionCsv($submissions);

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'PPC Programme Submission Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

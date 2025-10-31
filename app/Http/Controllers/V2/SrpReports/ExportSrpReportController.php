<?php

namespace App\Http\Controllers\V2\SrpReports;

use App\Http\Controllers\Controller;
use App\Models\V2\SrpReport;
use Illuminate\Http\Request;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportSrpReportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $header = [
            'ID', 'UUID', 'Project UUID', 'Project Name', 'Status',
            'Other Restoration Partners Description', 'Total Unique Restoration Partners',
            'Year', 'Created At', 'Updated At', 'Submitted At',
        ];
        $records = [];

        $reports = SrpReport::with(['project'])->get();

        foreach ($reports as $report) {
            $records[] = [
                $report->id,
                $report->uuid,
                $report->project->uuid,
                $report->project->name ?? null,
                $report->status,
                $report->other_restoration_partners_description,
                $report->total_unique_restoration_partners,
                $report->year,
                $report->created_at,
                $report->updated_at,
                $report->submitted_at,
            ];
        }

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Annual Socio Economic Restoration Reports Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

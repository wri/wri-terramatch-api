<?php

namespace App\Http\Controllers\V2\DisturbanceReports;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\DisturbanceReports\ExportDisturbanceReportRequest;
use App\Models\V2\DisturbanceReport;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDisturbanceReportController extends Controller
{
    public function __invoke(ExportDisturbanceReportRequest $exportDisturbanceReportRequest): StreamedResponse
    {
        $header = [
            'ID', 'UUID', 'Project ID', 'Project Name', 'Status',
            'Date of Incident', 'Intensity', 'Title', 'Due At', 'Submitted At',
            'Created At', 'Updated At',
        ];
        $records = [];

        $reports = DisturbanceReport::with(['project'])->get();

        foreach ($reports as $report) {
            $answers = collect($report->answers ?? [])->map(function ($answer) {
                return "{$answer->question}:{$answer->answer}";
            })->implode('|');

            $feedbackFields = collect($report->feedback_fields ?? [])->map(function ($feedbackField) {
                return "{$feedbackField->question}:{$feedbackField->answer}";
            })->implode('|');

            $records[] = [
                $report->id,
                $report->uuid,
                $report->project_id,
                $report->project->name ?? null,
                $report->status,
                $report->date_of_incident,
                $report->intensity,
                $report->title,
                $report->due_at,
                $report->submitted_at,
                $report->created_at,
                $report->updated_at,
            ];
        }

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, 'Disturbance Reports Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

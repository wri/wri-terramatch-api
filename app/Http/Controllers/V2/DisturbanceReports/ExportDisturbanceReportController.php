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
            'ID', 'UUID', 'Project UUID', 'Project Name', 'Status',
            'Date of Incident', 'Date of Disturbance', 'Extent', 'Property Affected',
            'People Affected', 'Monetary Damage', 'Description', 'Action Description',
            'Disturbance Type', 'Disturbance Subtype', 'Intensity',
            'Created At', 'Updated At', 'Submitted At',
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
                $report->project->uuid,
                $report->project->name ?? null,
                $report->status,
                $report->date_of_incident,
                $report->date_of_disturbance,
                $report->extent,
                is_array($report->property_affected) ? implode(',', $report->property_affected) : $report->property_affected,
                $report->people_affected,
                $report->monetary_damage,
                $report->description,
                $report->action_description,
                $report->disturbance_type,
                is_array($report->disturbance_subtype) ? implode(',', $report->disturbance_subtype) : $report->disturbance_subtype,
                $report->intensity,
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
        }, 'Disturbance Reports Export - ' . now() . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

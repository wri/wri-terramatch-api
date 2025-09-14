<?php

namespace App\Http\Controllers\V2\DisturbanceReports;

use App\Http\Controllers\Controller;
use App\Models\V2\DisturbanceReport;
use Illuminate\Http\Request;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDisturbanceReportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $header = [
            'ID', 'UUID', 'Project UUID', 'Project Name', 'Status',
            'Date of Disturbance', 'Extent', 'Property Affected',
            'People Affected', 'Monetary Damage', 'Description', 'Action Description',
            'Disturbance Type', 'Disturbance Subtype', 'Intensity', 'Site Affected', 'Polygon Affected',
            'Media Files', 'Created At', 'Updated At', 'Submitted At',
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
                $this->formatSiteAffected($report->site_affected),
                $this->formatPolygonAffected($report->polygon_affected),
                $this->formatMediaFiles($report),
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

    private function formatSiteAffected($siteAffected): string
    {
        if (empty($siteAffected) || ! is_array($siteAffected)) {
            return '';
        }

        $formatted = [];
        foreach ($siteAffected as $site) {
            if (is_object($site) && isset($site->siteName)) {
                $formatted[] = $site->siteName;
            } elseif (is_array($site) && isset($site['siteName'])) {
                $formatted[] = $site['siteName'];
            }
        }

        return implode(', ', $formatted);
    }

    private function formatPolygonAffected($polygonAffected): string
    {
        if (empty($polygonAffected) || ! is_array($polygonAffected)) {
            return '';
        }

        $formatted = [];
        foreach ($polygonAffected as $polygonGroup) {
            if (is_array($polygonGroup)) {
                foreach ($polygonGroup as $polygon) {
                    if (is_object($polygon) && isset($polygon->polyName)) {
                        $formatted[] = $polygon->polyName;
                    } elseif (is_array($polygon) && isset($polygon['polyName'])) {
                        $formatted[] = $polygon['polyName'];
                    }
                }
            }
        }

        return implode(', ', $formatted);
    }

    private function formatMediaFiles($report): string
    {
        try {
            $mediaFiles = $report->getMedia('media');

            if ($mediaFiles->isEmpty()) {
                return '';
            }

            $formatted = [];
            foreach ($mediaFiles as $media) {
                $formatted[] = $media->getUrl() . ' (' . $media->name . ')';
            }

            return implode(', ', $formatted);
        } catch (\Exception $e) {
            return 'Error loading media files';
        }
    }
}

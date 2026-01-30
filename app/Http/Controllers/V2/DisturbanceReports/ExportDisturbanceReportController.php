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

        $reports = DisturbanceReport::with(['project', 'entries'])->get();

        foreach ($reports as $report) {
            $records[] = [
                $report->id,
                $report->uuid,
                $report->project->uuid,
                $report->project->name ?? null,
                $report->status,
                $this->formatEntries($report->entries, 'date-of-disturbance'),
                $this->formatEntries($report->entries, 'extent'),
                $this->formatEntries($report->entries, 'property-affected'),
                $this->formatEntries($report->entries, 'people-affected'),
                $this->formatEntries($report->entries, 'monetary-damage'),
                $report->description,
                $report->action_description,
                $this->formatEntries($report->entries, 'disturbance-type'),
                $this->formatEntries($report->entries, 'disturbance-subtype'),
                $this->formatEntries($report->entries, 'intensity'),
                $this->formatEntries($report->entries, 'site-affected'),
                $this->formatEntries($report->entries, 'polygon-affected'),
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

    private function formatEntries($entries, $name): string
    {
        $entriesData = $entries->filter(function ($entry) use ($name) {
            return $entry->name === $name;
        })->map(function ($entry) use ($name) {
            if ($name == 'site-affected') {
                return $this->formatSiteAffected($entry->value);
            } elseif ($name == 'polygon-affected') {
                return $this->formatPolygonAffected($entry->value);
            } else {
                return  is_array($entry->value) ? implode(', ', $entry->value) : $entry->value;
            }
        })->implode('|');

        return $entriesData;
    }

    private function formatSiteAffected($siteAffected): string
    {
        if (is_string($siteAffected) && $this->isJson($siteAffected)) {
            $siteAffected = json_decode($siteAffected, true);
        }

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
        if (is_string($polygonAffected) && $this->isJson($polygonAffected)) {
            $polygonAffected = json_decode($polygonAffected, true);
        }

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

    private function isJson($string): bool
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
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

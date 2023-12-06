<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundProgramme as TerrafundProgrammeModel;
use App\Resources\Resource;

class TerrafundProgrammeLiteResource extends Resource
{
    public function __construct(TerrafundProgrammeModel $programme)
    {
        $this->id = $programme->id;
        $this->name = $programme->name;
        $this->project_country = $programme->project_country;
        $this->home_country = $programme->home_country;
        $this->boundary_geojson = $programme->boundary_geojson;
        $this->next_due_at = $programme->next_due_submission ? $programme->next_due_submission->due_at : null;
        $this->latest_report_date = $this->getLatestReportDate($programme);
        $this->created_at = $programme->created_at;
        $this->updated_at = $programme->updated_at;
    }

    private function getLatestReportDate($programme)
    {
        return $programme->terrafundProgrammeSubmissions()->max('updated_at');
    }
}

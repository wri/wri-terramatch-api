<?php

namespace App\Resources;

use App\Models\Programme as ProgrammeModel;

class ProgrammeLiteResource extends Resource
{
    public function __construct(ProgrammeModel $programme)
    {
        $this->id = $programme->id;
        $this->name = $programme->name;
        $this->country = $programme->country;
        $this->boundary_geojson = $programme->boundary_geojson;
        $this->continent = $programme->continent;
        $this->latest_report_date = $this->getLatestReportDate($programme);
        $this->created_at = $programme->updated_at;
        $this->updated_at = $programme->updated_at;
    }

    private function getLatestReportDate($programme)
    {
        return $programme->submissions()->max('updated_at');
    }
}

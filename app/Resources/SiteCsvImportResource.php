<?php

namespace App\Resources;

use App\Models\SiteCsvImport as SiteCsvImportModel;

class SiteCsvImportResource extends Resource
{
    public function __construct(SiteCsvImportModel $csvImport)
    {
        $this->id = $csvImport->id;
        $this->site_id = $csvImport->site_id;
        $this->total_rows = $csvImport->total_rows;
        $this->completed_rows = $csvImport->siteTreeSpecies->count();
        $this->has_failed = $csvImport->has_failed;
        $this->created_at = $csvImport->created_at;
        $this->updated_at = $csvImport->updated_at;
    }
}

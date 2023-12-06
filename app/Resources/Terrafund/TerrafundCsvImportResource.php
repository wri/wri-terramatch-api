<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundCsvImport as TerrafundCsvImportModel;
use App\Resources\Resource;

class TerrafundCsvImportResource extends Resource
{
    public function __construct(TerrafundCsvImportModel $csvImport)
    {
        $this->id = $csvImport->id;
        $this->importable_type = $csvImport->importable_type;
        $this->importable_id = $csvImport->importable_id;
        $this->total_rows = $csvImport->total_rows;
        $this->completed_rows = $csvImport->terrafundTreeSpecies->count();
        $this->has_failed = $csvImport->has_failed;
        $this->created_at = $csvImport->created_at;
        $this->updated_at = $csvImport->updated_at;
    }
}

<?php

namespace App\Resources;

use App\Models\CsvImport as CsvImportModel;

class CsvImportResource extends Resource
{
    public function __construct(CsvImportModel $csvImport)
    {
        $this->id = $csvImport->id;
        $this->status = $csvImport->status;
        $this->programme_id = $csvImport->programme_id;
        $this->total_rows = $csvImport->total_rows;
        $this->completed_rows = $csvImport->programmeTreeSpecies->count();
        $this->created_at = $csvImport->created_at;
        $this->updated_at = $csvImport->updated_at;
    }
}

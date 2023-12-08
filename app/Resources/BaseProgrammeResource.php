<?php

namespace App\Resources;

use App\Models\Programme as ProgrammeModel;

class BaseProgrammeResource extends Resource
{
    public function __construct(ProgrammeModel $programme)
    {
        $this->id = $programme->id;
        $this->name = $programme->name;
        $this->country = $programme->country;
        $this->continent = $programme->continent;
        $this->created_at = $programme->created_at;
        $this->updated_at = $programme->updated_at;
    }
}

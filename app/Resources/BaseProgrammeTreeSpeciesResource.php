<?php

namespace App\Resources;

use App\Models\ProgrammeTreeSpecies as ProgrammeTreeSpeciesModel;

class BaseProgrammeTreeSpeciesResource extends Resource
{
    public function __construct(ProgrammeTreeSpeciesModel $programmeTreeSpecies)
    {
        $this->id = $programmeTreeSpecies->id;
        $this->programme_id = $programmeTreeSpecies->programme_id;
        $this->name = $programmeTreeSpecies->name;
        $this->created_at = $programmeTreeSpecies->created_at;
    }
}

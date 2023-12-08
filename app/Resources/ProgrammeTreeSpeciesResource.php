<?php

namespace App\Resources;

use App\Models\ProgrammeTreeSpecies as ProgrammeTreeSpeciesModel;

class ProgrammeTreeSpeciesResource extends Resource
{
    public function __construct(ProgrammeTreeSpeciesModel $programmeTreeSpecies)
    {
        $this->id = $programmeTreeSpecies->id;
        $this->programme_id = $programmeTreeSpecies->programme_id;
        $this->programme_submission_id = $programmeTreeSpecies->programme_submission_id;
        $this->name = $programmeTreeSpecies->name;
        $this->amount = $programmeTreeSpecies->amount;
        $this->created_at = $programmeTreeSpecies->created_at;
    }
}

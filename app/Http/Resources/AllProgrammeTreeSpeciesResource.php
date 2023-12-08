<?php

namespace App\Http\Resources;

use App\Resources\Resource;

class AllProgrammeTreeSpeciesResource extends Resource
{
    public function __construct($speciesResource)
    {
        $this->programme_id = $speciesResource[0]->programme_id ?? null;
        $this->trees = $speciesResource;
    }
}

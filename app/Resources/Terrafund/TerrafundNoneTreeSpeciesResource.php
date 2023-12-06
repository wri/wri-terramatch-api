<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundNoneTreeSpecies as TerrafundNoneTreeSpeciesModel;
use App\Resources\Resource;

class TerrafundNoneTreeSpeciesResource extends Resource
{
    public function __construct(TerrafundNoneTreeSpeciesModel $noneTreeSpecies)
    {
        $this->id = $noneTreeSpecies->id;
        $this->speciesable_type = $noneTreeSpecies->speciesable_type;
        $this->speciesable_id = $noneTreeSpecies->speciesable_id;
        $this->name = $noneTreeSpecies->name;
        $this->amount = $noneTreeSpecies->amount;
        $this->created_at = $noneTreeSpecies->created_at;
        $this->updated_at = $noneTreeSpecies->updated_at;
    }
}

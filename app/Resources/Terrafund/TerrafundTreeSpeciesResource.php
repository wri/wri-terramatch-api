<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundTreeSpecies as TerrafundTreeSpeciesModel;
use App\Resources\Resource;

class TerrafundTreeSpeciesResource extends Resource
{
    public function __construct(TerrafundTreeSpeciesModel $treeSpecies)
    {
        $this->id = $treeSpecies->id;
        $this->treeable_type = $treeSpecies->treeable_type;
        $this->treeable_id = $treeSpecies->treeable_id;
        $this->name = $treeSpecies->name;
        $this->amount = $treeSpecies->amount;
        $this->percentage = $treeSpecies->percentage;
        $this->created_at = $treeSpecies->created_at;
        $this->updated_at = $treeSpecies->updated_at;
    }
}

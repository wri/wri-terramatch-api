<?php

namespace App\Resources;

use App\Models\LandTenure as LandTenureModel;

class LandTenureResource extends Resource
{
    public function __construct(LandTenureModel $landTenure)
    {
        $this->id = $landTenure->id;
        $this->name = $landTenure->name;
        $this->key = $landTenure->key;
        $this->created_at = $landTenure->created_at;
        $this->updated_at = $landTenure->updated_at;
    }
}

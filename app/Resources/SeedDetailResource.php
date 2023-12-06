<?php

namespace App\Resources;

use App\Models\SeedDetail as SeedDetailModel;

class SeedDetailResource extends Resource
{
    public function __construct(SeedDetailModel $seedDetail)
    {
        $this->id = $seedDetail->id;
        $this->name = $seedDetail->name;
        $this->weight_of_sample = $seedDetail->weight_of_sample;
        $this->seeds_in_sample = $seedDetail->seeds_in_sample;
        $this->seeds_per_kg = $seedDetail->seeds_per_kg;
        $this->site_id = $seedDetail->site_id;
        $this->created_at = $seedDetail->created_at;
        $this->updated_at = $seedDetail->updated_at;
    }
}

<?php

namespace App\Resources;

use App\Models\Aim as AimModel;

class AimResource extends Resource
{
    public function __construct(AimModel $aim)
    {
        $this->id = $aim->id;
        $this->programme_id = $aim->programme_id;
        $this->year_five_trees = $aim->year_five_trees;
        $this->restoration_hectares = $aim->restoration_hectares;
        $this->survival_rate = $aim->survival_rate;
        $this->year_five_crown_cover = $aim->year_five_crown_cover;
        $this->submitted_tree_count = $aim->programme->submitted_tree_count;
        $this->created_at = $aim->created_at;
        $this->updated_at = $aim->updated_at;
    }
}

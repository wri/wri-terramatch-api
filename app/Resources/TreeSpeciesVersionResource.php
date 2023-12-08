<?php

namespace App\Resources;

use App\Models\TreeSpecies as ParentModel;
use App\Models\TreeSpeciesVersion as ChildModel;

class TreeSpeciesVersionResource extends Resource
{
    public function __construct(ParentModel $parentModel, ChildModel $childModel)
    {
        $this->id = $childModel->id;
        $this->status = $childModel->status;
        $this->approved_rejected_by = $childModel->approved_rejected_by;
        $this->approved_rejected_at = $childModel->approved_rejected_at;
        $this->rejected_reason = $childModel->rejected_reason;
        $this->rejected_reason_body = $childModel->rejected_reason_body;
        $this->created_at = $childModel->created_at;
        $this->updated_at = $childModel->updated_at;
        $this->data = (object) [
            'id' => $parentModel->id,
            'pitch_id' => $parentModel->pitch_id,
            'name' => $childModel->name,
            'is_native' => $childModel->is_native,
            'count' => $childModel->count,
            'price_to_plant' => $childModel->price_to_plant,
            'price_to_maintain' => $childModel->price_to_maintain,
            'saplings' => $childModel->saplings,
            'site_prep' => $childModel->site_prep,
            'survival_rate' => $childModel->survival_rate,
            'produces_food' => $childModel->produces_food,
            'produces_firewood' => $childModel->produces_firewood,
            'produces_timber' => $childModel->produces_timber,
            'owner' => $childModel->owner,
            'season' => $childModel->season,
            'created_at' => $parentModel->created_at,
            'updated_at' => $childModel->created_at,
        ];
    }
}

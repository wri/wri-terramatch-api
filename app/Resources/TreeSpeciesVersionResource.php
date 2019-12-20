<?php

namespace App\Resources;

use App\Models\TreeSpecies as ParentModel;
use App\Models\TreeSpeciesVersion as ChildModel;

class TreeSpeciesVersionResource extends Resource
{
    public $id = null;
    public $status = null;
    public $approved_rejected_by = null;
    public $approved_rejected_at = null;
    public $rejected_reason = null;
    public $data = null;

    public function __construct(ParentModel $parentModel, ChildModel $childModel)
    {
        $this->id = $childModel->id;
        $this->status = $childModel->status;
        $this->approved_rejected_by = $childModel->approved_rejected_by;
        $this->approved_rejected_at = $childModel->approved_rejected_at;
        $this->rejected_reason = $childModel->rejected_reason;
        $this->data = (object) [
            "id" => $parentModel->id,
            "pitch_id" => $parentModel->pitch_id,
            "name" => $childModel->name,
            "is_native" => $childModel->is_native,
            "count" => $childModel->count,
            "price_to_plant" => $childModel->price_to_plant,
            "price_to_maintain" => $childModel->price_to_maintain,
            "saplings" => $childModel->saplings,
            "site_prep" => $childModel->site_prep,
            "survival_rate" => $childModel->survival_rate,
            "produces_food" => $childModel->produces_food,
            "produces_firewood" => $childModel->produces_firewood,
            "produces_timber" => $childModel->produces_timber,
            "owner" => $childModel->owner,
            "season" => $childModel->season
        ];
    }
}

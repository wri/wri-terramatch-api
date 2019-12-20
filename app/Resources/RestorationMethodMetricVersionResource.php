<?php

namespace App\Resources;

use App\Models\RestorationMethodMetric as ParentModel;
use App\Models\RestorationMethodMetricVersion as ChildModel;

class RestorationMethodMetricVersionResource extends Resource
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
            "restoration_method" => $childModel->restoration_method,
            "experience" => $childModel->experience,
            "land_size" => $childModel->land_size,
            "price_per_hectare" => $childModel->price_per_hectare,
            "biomass_per_hectare" => $childModel->biomass_per_hectare,
            "carbon_impact" => $childModel->carbon_impact,
            "species_impacted" => $childModel->species_impacted,
        ];
    }
}
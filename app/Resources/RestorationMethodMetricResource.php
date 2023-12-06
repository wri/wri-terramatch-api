<?php

namespace App\Resources;

use App\Models\RestorationMethodMetric as ParentModel;
use App\Models\RestorationMethodMetricVersion as ChildModel;

class RestorationMethodMetricResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->pitch_id = $parentModel->pitch_id;
        $this->restoration_method = $childModel->restoration_method ?? null;
        $this->experience = $childModel->experience ?? null;
        $this->land_size = $childModel->land_size ?? null;
        $this->price_per_hectare = $childModel->price_per_hectare ?? null;
        $this->biomass_per_hectare = $childModel->biomass_per_hectare ?? null;
        $this->carbon_impact = $childModel->carbon_impact ?? null;
        $this->species_impacted = $childModel->species_impacted ?? [];
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $childModel->created_at ?? null;
    }
}

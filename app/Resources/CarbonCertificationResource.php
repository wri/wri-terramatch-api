<?php

namespace App\Resources;

use App\Models\CarbonCertification as ParentModel;
use App\Models\CarbonCertificationVersion as ChildModel;

class CarbonCertificationResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->pitch_id = $parentModel->pitch_id;
        $this->type = $childModel->type ?? null;
        $this->other_value = $childModel->other_value ?? null;
        $this->link = $childModel->link ?? null;
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $childModel->created_at ?? null;
    }
}

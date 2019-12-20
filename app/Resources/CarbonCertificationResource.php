<?php

namespace App\Resources;

use App\Models\CarbonCertification as ParentModel;
use App\Models\CarbonCertificationVersion as ChildModel;

class CarbonCertificationResource extends Resource
{
    public $id = null;
    public $pitch_id = null;
    public $type = null;
    public $other_type = null;
    public $link = null;

    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->pitch_id = $parentModel->pitch_id;
        $this->type = $childModel->type ?? null;
        $this->other_type = $childModel->other_type ?? null;
        $this->link = $childModel->link ?? null;
    }
}

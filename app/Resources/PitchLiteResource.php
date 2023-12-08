<?php

namespace App\Resources;

use App\Models\Pitch as ParentModel;
use App\Models\PitchVersion as ChildModel;

class PitchLiteResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel, bool $displayCompatibilityScore = false)
    {
        $this->id = $parentModel->id;
        $this->name = $childModel->name ?? null;
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $childModel->created_at ?? null;
    }
}

<?php

namespace App\Resources;

use App\Models\PitchDocument as ParentModel;
use App\Models\PitchDocumentVersion as ChildModel;

class PitchDocumentResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->pitch_id = $parentModel->pitch_id;
        $this->name = $childModel->name ?? null;
        $this->type = $childModel->type ?? null;
        $this->document = $childModel->document ?? null;
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $childModel->created_at ?? null;
    }
}

<?php

namespace App\Resources;

use App\Models\PitchDocument as ParentModel;
use App\Models\PitchDocumentVersion as ChildModel;

class PitchDocumentResource extends Resource
{
    public $id = null;
    public $pitch_id = null;
    public $name = null;
    public $type = null;
    public $document = null;

    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->pitch_id = $parentModel->pitch_id;
        $this->name = $childModel->name ?? null;
        $this->type = $childModel->type ?? null;
        $this->document = $childModel->document ?? null;
    }
}
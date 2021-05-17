<?php

namespace App\Resources;

use App\Models\Draft as DraftModel;
use App\Helpers\DraftHelper;

class DraftResource extends Resource
{
    public function __construct(DraftModel $draft)
    {
        $this->id = $draft->id;
        $this->organisation_id = $draft->organisation_id;
        $this->name = $draft->name;
        $this->type = $draft->type;
        $this->data = DraftHelper::transformUploads($draft->type, json_decode($draft->data));
        $this->created_at = $draft->created_at;
        $this->created_by = $draft->created_by;
        $this->updated_at = $draft->updated_at;
        $this->updated_by = $draft->updated_by;
    }
}

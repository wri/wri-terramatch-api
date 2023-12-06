<?php

namespace App\Resources;

use App\Helpers\DraftHelper;
use App\Models\Draft as DraftModel;

class DraftResource extends Resource
{
    public function __construct(DraftModel $draft)
    {
        $drafting = DraftHelper::drafting($draft->type);

        $this->id = $draft->id;
        $this->organisation_id = $draft->organisation_id;
        $this->name = $draft->name;
        $this->type = $draft->type;
        $this->is_from_mobile = $draft->is_from_mobile ?: false;
        $this->completed_elsewhere = $drafting::draftSubmissionHasBeenCompleted($draft);
        $this->is_merged = $draft->is_merged;
        $this->data = $drafting::transformUploads(json_decode($draft->data));
        $this->created_at = $draft->created_at;
        $this->created_by = $draft->created_by;
        $this->updated_at = $draft->updated_at;
        $this->updated_by = $draft->updated_by;
    }
}

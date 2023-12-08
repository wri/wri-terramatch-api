<?php

namespace App\Resources;

use App\Models\OrganisationDocument as ParentModel;
use App\Models\OrganisationDocumentVersion as ChildModel;

class OrganisationDocumentVersionResource extends Resource
{
    public function __construct(ParentModel $parentModel, ChildModel $childModel)
    {
        $this->id = $childModel->id;
        $this->status = $childModel->status;
        $this->approved_rejected_by = $childModel->approved_rejected_by;
        $this->approved_rejected_at = $childModel->approved_rejected_at;
        $this->rejected_reason = $childModel->rejected_reason;
        $this->rejected_reason_body = $childModel->rejected_reason_body;
        $this->created_at = $childModel->created_at;
        $this->updated_at = $childModel->updated_at;
        $this->data = (object) [
            'id' => $parentModel->id,
            'organisation_id' => $parentModel->organisation_id,
            'name' => $childModel->name,
            'type' => $childModel->type,
            'document' => $childModel->document,
            'created_at' => $parentModel->created_at,
            'updated_at' => $childModel->created_at,
        ];
    }
}

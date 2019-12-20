<?php

namespace App\Resources;

use App\Models\OrganisationDocument as ParentModel;
use App\Models\OrganisationDocumentVersion as ChildModel;

class OrganisationDocumentVersionResource extends Resource
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
            "organisation_id" => $parentModel->organisation_id,
            "name" => $childModel->name,
            "type" => $childModel->type,
            "document" => $childModel->document
        ];
    }
}
<?php

namespace App\Resources;

use App\Models\OrganisationDocument as ParentModel;
use App\Models\OrganisationDocumentVersion as ChildModel;

class OrganisationDocumentResource extends Resource
{
    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->organisation_id = $parentModel->organisation_id;
        $this->name = $childModel->name ?? null;
        $this->type = $childModel->type ?? null;
        $this->document = $childModel->document ?? null;
        $this->created_at = $parentModel->created_at;
        $this->updated_at = $childModel->created_at ?? null;
    }
}

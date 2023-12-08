<?php

namespace App\Resources;

use App\Models\OrganisationPhoto as OrganisationPhotoModel;

class OrganisationPhotoResource extends Resource
{
    public function __construct(OrganisationPhotoModel $organisationPhoto)
    {
        $this->id = $organisationPhoto->id;
        $this->upload = $organisationPhoto->upload;
        $this->is_public = $organisationPhoto->is_public;
        $this->organisation_id = $organisationPhoto->organisation_id;
        $this->created_at = $organisationPhoto->created_at;
        $this->updated_at = $organisationPhoto->updated_at;
    }
}

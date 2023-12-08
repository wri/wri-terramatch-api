<?php

namespace App\Resources;

use App\Models\OrganisationFile as OrganisationFileModel;

class OrganisationFileResource extends Resource
{
    public function __construct(OrganisationFileModel $organisationFile)
    {
        $this->id = $organisationFile->id;
        $this->upload = $organisationFile->upload;
        $this->organisation_id = $organisationFile->organisation_id;
        $this->type = $organisationFile->type;
        $this->created_at = $organisationFile->created_at;
        $this->updated_at = $organisationFile->updated_at;
    }
}

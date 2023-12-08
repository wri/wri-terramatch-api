<?php

namespace App\Resources;

use App\Models\DirectSeeding as DirectSeedingModel;

class DirectSeedingResource extends Resource
{
    public function __construct(DirectSeedingModel $directSeeding)
    {
        $this->id = $directSeeding->id;
        $this->site_submission_id = $directSeeding->site_submission_id;
        $this->name = $directSeeding->name;
        $this->weight = $directSeeding->weight;
        $this->created_at = $directSeeding->created_at;
        $this->updated_at = $directSeeding->updated_at;
    }
}

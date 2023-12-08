<?php

namespace App\Resources;

use App\Models\SiteTreeSpecies as SiteTreeSpeciesModel;

class SiteTreeSpeciesResource extends Resource
{
    public function __construct(SiteTreeSpeciesModel $siteTreeSpecies)
    {
        $this->id = $siteTreeSpecies->id;
        $this->site_id = $siteTreeSpecies->site_id;
        $this->site_submission_id = $siteTreeSpecies->site_submission_id;
        $this->name = $siteTreeSpecies->name;
        $this->amount = $siteTreeSpecies->amount;
        $this->created_at = $siteTreeSpecies->created_at;
    }
}

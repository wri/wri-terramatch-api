<?php

namespace App\Resources;

use App\Models\SiteTreeSpecies as SiteTreeSpeciesModel;

class BaseSiteTreeSpeciesResource extends Resource
{
    public function __construct(SiteTreeSpeciesModel $siteTreeSpecies)
    {
        $this->id = $siteTreeSpecies->id;
        $this->site_id = $siteTreeSpecies->site_id;
        $this->name = $siteTreeSpecies->name;
        $this->created_at = $siteTreeSpecies->created_at;
    }
}

<?php

namespace App\Http\Resources;

use App\Resources\Resource;

class AllSiteTreeSpeciesResource extends Resource
{
    public function __construct($speciesResource)
    {
        $this->site_id = $speciesResource[0]->site_id ?? null;
        $this->trees = $speciesResource;
    }
}

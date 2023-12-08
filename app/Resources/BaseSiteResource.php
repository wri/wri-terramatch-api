<?php

namespace App\Resources;

use App\Models\Site as SiteModel;

class BaseSiteResource extends Resource
{
    public function __construct(SiteModel $site)
    {
        $this->id = $site->id;
        $this->programme_id = $site->programme_id;
        $this->control_site = $site->control_site;
        $this->programme_name = $site->programme->name;
        $this->name_with_id = $site->name_with_id;
        $this->name = $site->name;
        $this->country = $site->programme->country;
        $this->continent = $site->programme->continent;
        $this->created_at = $site->created_at;
        $this->updated_at = $site->updated_at;
    }
}

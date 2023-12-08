<?php

namespace App\Resources;

use App\Models\SiteRestorationMethod as SiteRestorationMethodModel;

class SiteRestorationMethodResource extends Resource
{
    public function __construct(SiteRestorationMethodModel $siteRestorationMethod)
    {
        $this->id = $siteRestorationMethod->id;
        $this->name = $siteRestorationMethod->name;
        $this->key = $siteRestorationMethod->key;
        $this->created_at = $siteRestorationMethod->created_at;
        $this->updated_at = $siteRestorationMethod->updated_at;
    }
}

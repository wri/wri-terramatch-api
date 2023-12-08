<?php

namespace App\Http\Resources\V2\Sites;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SitesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => SiteResource::collection($this->collection)];
    }
}

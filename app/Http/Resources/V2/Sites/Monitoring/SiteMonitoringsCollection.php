<?php

namespace App\Http\Resources\V2\Sites\Monitoring;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SiteMonitoringsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => SiteMonitoringResource::collection($this->collection)];
    }
}

<?php

namespace App\Http\Resources\V2\Sites;

use Illuminate\Http\Resources\Json\ResourceCollection;

class V2SitePolygonsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}

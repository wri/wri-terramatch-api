<?php

namespace App\Http\Resources\V2\Polygons;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PolygonsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => PolygonResource::collection($this->collection)];
    }
}

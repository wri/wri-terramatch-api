<?php

namespace App\Http\Resources\V2\Polygons;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GeojsonCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => GeojsonResource::collection($this->collection)];
    }
}

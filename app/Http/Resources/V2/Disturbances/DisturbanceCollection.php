<?php

namespace App\Http\Resources\V2\Disturbances;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DisturbanceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => DisturbanceResource::collection($this->collection)];
    }
}

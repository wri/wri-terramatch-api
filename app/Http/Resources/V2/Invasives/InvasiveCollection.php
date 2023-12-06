<?php

namespace App\Http\Resources\V2\Invasives;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InvasiveCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => InvasiveResource::collection($this->collection)];
    }
}

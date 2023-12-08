<?php

namespace App\Http\Resources\V2\Seedings;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SeedingsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => SeedingResource::collection($this->collection)];
    }
}

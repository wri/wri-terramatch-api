<?php

namespace App\Http\Resources\V2\General;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ListingCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ListingResource::collection($this->collection)];
    }
}

<?php

namespace App\Http\Resources\V2\PPC;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PPCSitesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => PPCSiteResource::collection($this->collection)];
    }
}

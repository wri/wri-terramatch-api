<?php

namespace App\Http\Resources\V2\Applications;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ApplicationsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => ApplicationLiteResource::collection($this->collection)];
    }
}

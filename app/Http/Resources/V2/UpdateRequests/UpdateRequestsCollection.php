<?php

namespace App\Http\Resources\V2\UpdateRequests;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UpdateRequestsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => UpdateRequestLiteResource::collection($this->collection)];
    }
}

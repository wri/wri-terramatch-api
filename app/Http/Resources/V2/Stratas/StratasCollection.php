<?php

namespace App\Http\Resources\V2\Stratas;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StratasCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => StrataResource::collection($this->collection)];
    }
}

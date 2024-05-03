<?php

namespace App\Http\Resources\V2\Workdays;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WorkdaysCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => WorkdayResource::collection($this->collection)];
    }
}

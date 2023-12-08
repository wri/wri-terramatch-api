<?php

namespace App\Http\Resources\V2\Tasks;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TasksCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => TaskResource::collection($this->collection)];
    }
}

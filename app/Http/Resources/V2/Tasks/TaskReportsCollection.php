<?php

namespace App\Http\Resources\V2\Tasks;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskReportsCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => TaskReportResource::collection($this->collection)];
    }
}

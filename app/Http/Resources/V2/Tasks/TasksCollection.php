<?php

namespace App\Http\Resources\V2\Tasks;

use App\Models\V2\Tasks\Task;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TasksCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => TaskResource::collection($this->collection)];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        $default['meta']['unfiltered_total'] = Task::count();

        return $default;
    }
}

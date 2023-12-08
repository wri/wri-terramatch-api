<?php

namespace App\Http\Controllers\V2\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Tasks\TaskResource;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\Request;

class ViewTaskController extends Controller
{
    public function __invoke(Request $request, Task $task): TaskResource
    {
        $project = $task->project;
        $this->authorize('read', $project);

        return new TaskResource($task);
    }
}

<?php

namespace App\Http\Controllers\V2\Tasks;

use App\Http\Controllers\Controller;
use App\Models\V2\Tasks\Task;

class ViewTaskUuidController extends Controller
{
    public function __invoke(int $taskId)
    {
        $task = Task::findOrFail($taskId);

        return response()->json(['uuid' => $task->uuid]);
    }
}

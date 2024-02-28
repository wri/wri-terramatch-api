<?php

namespace App\Http\Controllers\V2\Tasks;

use App\Exceptions\InvalidStatusException;
use App\Http\Controllers\Controller;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitProjectTasksController extends Controller
{
    /**
     * @throws InvalidStatusException
     */
    public function __invoke(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $task->submitForApproval();
        return new JsonResponse('Reports successfully submitted', 200);
    }
}

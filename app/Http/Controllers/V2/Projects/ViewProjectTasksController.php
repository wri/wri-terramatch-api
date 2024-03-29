<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Tasks\TasksCollection;
use App\Models\V2\Projects\Project;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ViewProjectTasksController extends Controller
{
    public function __invoke(Request $request, Project $project): TasksCollection
    {
        $this->authorize('read', $project);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $sortableColumns = [
            'status', '-status',
            'due_at', '-due_at',
        ];

        $query = QueryBuilder::for(Task::class)
            ->with('project')
            ->where('project_id', $project->id)
            ->defaultSort('-due_at');

        if (in_array($request->query('sort'), $sortableColumns)) {
            $query->allowedSorts($sortableColumns);
        }

        $collection = $query->paginate($perPage);

        return new TasksCollection($collection);
    }
}

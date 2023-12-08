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
            'period_key', '-period_key',
        ];

        $qry = QueryBuilder::for(Task::class)
            ->with(['project'])
            ->where('status', '!=', Task::STATUS_COMPLETE)
            ->where('project_id', $project->id);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        $collection = $qry->paginate($perPage);

        return new TasksCollection($collection);
    }
}

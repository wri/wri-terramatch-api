<?php

namespace App\Http\Controllers\V2\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\Tasks\TasksCollection;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexTasksController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request): TasksCollection
    {
        $this->authorize('readAll', Task::class);

        $query = QueryBuilder::for(Task::class)
            ->join('v2_projects', function($join) {
                $join->on('v2_tasks.project_id', '=', 'v2_projects.id');
            })
            ->selectRaw('
                v2_tasks.*,
                (SELECT name FROM organisations WHERE organisations.id = v2_tasks.organisation_id) as organisation_name
            ')
            ->allowedFilters([
                AllowedFilter::exact('framework_key'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('organisation_uuid', 'organisationUuid'),
            ]);

        $this->sort($query, [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'framework_key', '-framework_key',
            'due_at', '-due_at',
            'status', '-status',
            'organisation_name', '-organisation_name',
        ]);

        $this->isolateAuthorizedFrameworks($query, 'v2_projects');
        return new TasksCollection($this->paginate($query));
    }
}
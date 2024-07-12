<?php

namespace App\Http\Controllers\V2\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\Tasks\TasksCollection;
use App\Models\V2\Tasks\Task;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexTasksController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request): TasksCollection
    {
        $this->authorize('readAll', Task::class);

        $query = QueryBuilder::for(Task::class)
            ->join('v2_projects', function ($join) {
                $join->on('v2_tasks.project_id', '=', 'v2_projects.id');
            })
            ->selectRaw('
                v2_tasks.*,
                (SELECT name FROM organisations WHERE organisations.id = v2_projects.organisation_id) as organisation_name,
                (SELECT name from v2_projects WHERE v2_projects.id = v2_tasks.project_id) as project_name
            ')
            ->allowedFilters([
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('framework_key', 'frameworkKey'),
                AllowedFilter::exact('status'),
            ]);

        $this->sort($query, [
            'updated_at', '-updated_at',
            'due_at', '-due_at',
            'organisation_name', '-organisation_name',
            'project_name', '-project_name',
        ]);

        $user = User::find(Auth::user()->id);
        if ($user->primaryRole?->name == 'project-manager') {
            $query->whereIn('project_id', $user->managedProjects()->select('v2_projects.id'));
        } else {
            $this->isolateAuthorizedFrameworks($query, 'v2_projects');
        }

        return new TasksCollection($this->paginate($query));
    }
}

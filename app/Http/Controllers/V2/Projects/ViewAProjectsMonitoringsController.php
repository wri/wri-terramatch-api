<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\Monitoring\ProjectMonitoringsCollection;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ViewAProjectsMonitoringsController extends Controller
{
    public function __invoke(Request $request, Project $project): ProjectMonitoringsCollection
    {
        $this->authorize('read', $project);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $qry = QueryBuilder::for($project->monitoring());

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new ProjectMonitoringsCollection($collection);
    }
}

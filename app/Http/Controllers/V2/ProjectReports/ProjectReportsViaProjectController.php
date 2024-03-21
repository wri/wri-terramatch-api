<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectReports\ProjectReportsCollection;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectReportsViaProjectController extends Controller
{
    public function __invoke(Request $request, Project $project): ProjectReportsCollection
    {
        $this->authorize('read', $project);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'title', '-title',
            'due_at', '-due_at',
            'created_at', '-created_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(ProjectReport::class)
            ->allowedFilters([
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('framework_key'),
                AllowedFilter::exact('update_request_status'),
            ])
            ->where('project_id', $project->id)
            ->hasBeenSubmitted();

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = ProjectReport::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new ProjectReportsCollection($collection);
    }
}

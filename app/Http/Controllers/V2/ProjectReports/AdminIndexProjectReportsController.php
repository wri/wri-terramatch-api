<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectReports\ProjectReportsCollection;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexProjectReportsController extends Controller
{
    public function __invoke(Request $request): ProjectReportsCollection
    {
        $this->authorize('readAll', ProjectReport::class);
        $user = Auth::user();

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'title', '-title',
            'framework_key', '-framework_key',
            'organisation_name', '-organisation_name',
            'due_at', '-due_at',
            'created_at', '-created_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(ProjectReport::class)
            ->join('v2_projects', function ($join) {
                $join->on('v2_project_reports.project_id', '=', 'v2_projects.id');
            })
            ->selectRaw('
                v2_project_reports.*,
                (SELECT name FROM organisations WHERE organisations.id = v2_projects.organisation_id) as organisation_name
            ')
            ->allowedFilters([
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
                AllowedFilter::exact('framework_key'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = ProjectReport::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $qry->whereIn('v2_project_reports.id', $ids);
        }

        if (! $user->hasAllPermissions(['framework-ppc', 'framework-terrafund'])) {
            if ($user->hasPermissionTo('framework-terrafund')) {
                $qry->where('v2_project_reports.framework_key', 'terrafund');
            } elseif ($user->hasPermissionTo('framework-ppc')) {
                $qry->where('v2_project_reports.framework_key', 'ppc');
            }
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new ProjectReportsCollection($collection);
    }
}

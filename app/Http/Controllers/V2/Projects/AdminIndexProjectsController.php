<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectsCollection;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexProjectsController extends Controller
{
    public function __invoke(Request $request): ProjectsCollection
    {
        $this->authorize('readAll', Project::class);
        $user = Auth::user();

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'name', '-name',
            'status', '-status',
            'planting_start_date', '-planting_start_date',
            'organisation_name', '-organisation_name',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];

        $qry = QueryBuilder::for(Project::class)
            ->selectRaw('
                v2_projects.*,
                (SELECT name FROM organisations WHERE organisations.id = organisation_id) as organisation_name
            ')
            ->allowedFilters([
                AllowedFilter::exact('country'),
                AllowedFilter::exact('framework_key'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
                AllowedFilter::scope('organisation_uuid', 'organisationUuid'),
                AllowedFilter::scope('monitoring_data', 'hasMonitoringData'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = Project::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $qry->whereIn('v2_projects.id', $ids);
        }

        if (! $user->hasAllPermissions(['framework-ppc', 'framework-terrafund'])) {
            if ($user->hasPermissionTo('framework-terrafund')) {
                $qry->terrafund();
            } elseif ($user->hasPermissionTo('framework-ppc')) {
                $qry->ppc();
            }
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new ProjectsCollection($collection);
    }
}

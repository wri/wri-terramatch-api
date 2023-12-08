<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\V2SitesCollection;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexSitesController extends Controller
{
    public function __invoke(Request $request): V2SitesCollection
    {
        $this->authorize('readAll', Site::class);
        $user = Auth::user();

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $sortableColumns = [
            'name', '-name',
            'status', '-status',
            'project_name', '-project_name',
            'establishment_date', '-establishment_date',
            'start_date', '-start_date',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'deleted_at', '-deleted_at',
        ];

        $query = QueryBuilder::for(Site::class)
            ->selectRaw(
                '
                v2_sites.*,
                start_date AS establishment_date,
                (SELECT name FROM v2_projects WHERE v2_projects.id = project_id) as project_name,
                (SELECT name FROM organisations WHERE organisations.id = (SELECT organisation_id FROM v2_projects WHERE v2_projects.id = project_id)) as organisation_name
            '
            )
            ->allowedFilters([
                AllowedFilter::scope('country'),
                AllowedFilter::scope('organisation'),
                AllowedFilter::scope('organisation_uuid'),
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('monitoring_data', 'hasMonitoringData'),
                AllowedFilter::exact('framework_key'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
                AllowedFilter::trashed(),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $query->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = Site::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $query->whereIn('v2_sites.id', $ids);
        }

        if (! $user->hasAllPermissions(['framework-ppc', 'framework-terrafund'])) {
            if ($user->hasPermissionTo('framework-terrafund')) {
                $query->terrafund();
            } elseif ($user->hasPermissionTo('framework-ppc')) {
                $query->ppc();
            }
        }

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new V2SitesCollection($collection);
    }
}

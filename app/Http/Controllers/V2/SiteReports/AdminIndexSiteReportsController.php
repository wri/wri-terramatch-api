<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportsCollection;
use App\Models\Framework;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexSiteReportsController extends Controller
{
    public function __invoke(Request $request): SiteReportsCollection
    {
        $this->authorize('readAll', SiteReport::class);
        $user = Auth::user();

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'name', '-name',
            'framework_key', '-framework_key',
            'organisation_name', '-organisation_name',
            'due_at', '-due_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(SiteReport::class)
            ->join('v2_sites', function ($join) {
                $join->on('v2_site_reports.site_id', '=', 'v2_sites.id');
            })
            ->join('v2_projects', function ($join) {
                $join->on('v2_sites.project_id', '=', 'v2_projects.id');
            })
            ->selectRaw('
                v2_site_reports.*,
                (SELECT name FROM organisations WHERE organisations.id = v2_projects.organisation_id) as organisation_name
            ')
            ->allowedFilters([
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('site_uuid', 'siteUuid'),
                AllowedFilter::scope('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
                AllowedFilter::exact('framework_key'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = SiteReport::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $qry->whereIn('v2_site_reports.id', $ids);
        }

        $frameworks = Framework::all();

        $frameworkNamesWithPref = $frameworks->map(function ($framework) {
            return 'framework-' . $framework->slug;
        })->toArray();

        $frameworkNames = $frameworks->map(function ($framework) {
            return $framework->slug;
        })->toArray();

        if (! $user->hasAllPermissions($frameworkNamesWithPref)) {
            $qry->where(function ($query) use ($frameworkNames, $user) {
                foreach ($frameworkNames as $framework) {
                    $frameworkPermission = 'framework-' . $framework;
                    if ($user->hasPermissionTo($frameworkPermission)) {
                        $query->orWhere('v2_site_reports.framework_key', $framework);
                    }
                }
            });
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new SiteReportsCollection($collection);
    }
}

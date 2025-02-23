<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\SiteReports\SiteReportsCollection;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexSiteReportsController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request): SiteReportsCollection
    {
        $this->authorize('readAll', SiteReport::class);

        $query = QueryBuilder::for(SiteReport::class)
            ->join('v2_sites', function ($join) {
                $join->on('v2_site_reports.site_id', '=', 'v2_sites.id');
            })
            ->join('v2_projects', function ($join) {
                $join->on('v2_sites.project_id', '=', 'v2_projects.id');
            })
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
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
                AllowedFilter::scope('organisation_uuid', 'organisationUuid'),
            ]);

        $this->sort($query, [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'name', '-name',
            'framework_key', '-framework_key',
            'organisation_name', '-organisation_name',
            'due_at', '-due_at',
            'status', '-status',
        ]);

        if (! empty($request->query('search'))) {
            $ids = SiteReport::search(trim($request->query('search')))->pluck('id')->toArray();
            $query->whereIn('v2_site_reports.id', $ids);
        }

        $user = User::find(Auth::user()->id);
        if ($user->primaryRole?->name == 'project-manager') {
            $query->whereIn('v2_sites.project_id', $user->managedProjects()->select('v2_projects.id'));
        } else {
            $this->isolateAuthorizedFrameworks($query, 'v2_site_reports');
        }

        return new SiteReportsCollection($this->paginate($query));
    }
}

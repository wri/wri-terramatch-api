<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\Sites\V2SitesCollection;
use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexSitesController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request): V2SitesCollection
    {
        $this->authorize('readAll', Site::class);

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
                AllowedFilter::scope('polygon', 'filterByPolygonStatus'),
            ]);

        $this->sort($query, [
            'name', '-name',
            'status', '-status',
            'project_name', '-project_name',
            'establishment_date', '-establishment_date',
            'start_date', '-start_date',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'deleted_at', '-deleted_at',
        ]);

        if (! empty($request->query('search'))) {
            $search = trim($request->query('search'));
            if (is_numeric($search)) {
                $query->where('v2_sites.ppc_external_id', $search);
            } else {
                $ids = Site::search($search)->pluck('id')->toArray();
                $query->whereIn('v2_sites.id', $ids);
            }
        }

        $user = User::find(Auth::user()->id);
        if ($user->primaryRole?->name == 'project-manager') {
            $query->whereIn('project_id', $user->managedProjects()->select('v2_projects.id'));
        } else {
            $this->isolateAuthorizedFrameworks($query, 'v2_sites');
        }

        return new V2SitesCollection($this->paginate($query));
    }
}

<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\Sites\V2SitesCollection;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;
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
            $ids = Site::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $query->whereIn('v2_sites.id', $ids);
        }

        $this->isolateAuthorizedFrameworks($query, 'v2_sites');

        return new V2SitesCollection($this->paginate($query));
    }
}

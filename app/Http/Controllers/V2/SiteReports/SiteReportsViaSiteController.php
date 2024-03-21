<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportsCollection;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SiteReportsViaSiteController extends Controller
{
    public function __invoke(Request $request, Site $site): SiteReportsCollection
    {
        $this->authorize('read', $site);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'name', '-name',
            'due_at', '-due_at',
            'status', '-status',
        ];

        $qry = QueryBuilder::for(SiteReport::class)
            ->allowedFilters([
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('framework_key'),
            ])
            ->where('site_id', $site->id)
            ->hasBeenSubmitted();

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = SiteReport::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $qry->whereIn('v2_site_reports.id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new SiteReportsCollection($collection);
    }
}

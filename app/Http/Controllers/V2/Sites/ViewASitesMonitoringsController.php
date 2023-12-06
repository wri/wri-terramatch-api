<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\Monitoring\SiteMonitoringsCollection;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ViewASitesMonitoringsController extends Controller
{
    public function __invoke(Request $request, Site $site): SiteMonitoringsCollection
    {
        $this->authorize('read', $site);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $qry = QueryBuilder::for($site->monitoring());

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new SiteMonitoringsCollection($collection);
    }
}

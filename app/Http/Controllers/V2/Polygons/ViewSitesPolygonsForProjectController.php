<?php

namespace App\Http\Controllers\V2\Polygons;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Polygons\GeojsonCollection;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;

class ViewSitesPolygonsForProjectController extends Controller
{
    public function __invoke(Request $request, Project $project): GeojsonCollection
    {
        $this->authorize('read', $project);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $siteIds = $project->sites()->pluck('id')->toArray();

        if (empty($request->query('search'))) {
            $qry = Site::whereIn('id', $siteIds);
        } else {
            $qry = Site::search(trim($request->query('search')))
                ->whereIn('id', $siteIds);
        }

        $qry->orderBy('created_at', 'desc');

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new GeojsonCollection($collection);
    }
}

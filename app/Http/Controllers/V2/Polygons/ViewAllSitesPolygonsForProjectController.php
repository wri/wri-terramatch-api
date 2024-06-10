<?php

namespace App\Http\Controllers\V2\Polygons;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SitePolygon\SitePolygonResource;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ViewAllSitesPolygonsForProjectController extends Controller
{
    public function __invoke(Request $request, string $project): ResourceCollection
    {
        $sitePolygons = Project::where('uuid', $project)
            ->with('sitePolygons')
            ->get()
            ->pluck('sitePolygons')
            ->flatten();

        return SitePolygonResource::collection($sitePolygons);
    }
}

<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use app\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Http\Resources\V2\Dashboard\GetPolygonsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetPolygonsController extends Controller
{
    public function __invoke(Request $request): GetPolygonsResource
    {
        $projectIds = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->pluck('id');
        Log::info('Project ID: ' . $projectIds);
        $sitesIds = Site::whereIn('project_id', $projectIds)->pluck('uuid');
        $sitePolygonsIds = SitePolygon::whereIn('site_id', $sitesIds)->pluck('poly_id');
        $polygons = PolygonGeometry::whereIn('uuid', $sitePolygonsIds)->pluck('uuid');

        return new GetPolygonsResource([
            'data' => $polygons,
        ]);
    }
};

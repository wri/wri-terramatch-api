<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\GeometryHelper;
use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\GetPolygonsResource;
use App\Models\LandscapeGeom;
use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetPolygonsController extends Controller
{
    public function getCentroidOfPolygon(string $polyUuid)
    {
        $centroid = GeometryHelper::centroidOfPolygon($polyUuid);

        return response()->json(['centroid' => $centroid]);
    }

    public function getPolygonsDataByStatusOfProject(Request $request): GetPolygonsResource
    {
        $polygonsIdsByStatus = TerrafundDashboardQueryHelper::getPolygonsByStatusOfProjects($request);
        $polygonsIds = array_values($polygonsIdsByStatus)[0];
        $centroids = GeometryHelper::getCentroidsOfPolygons($polygonsIds);

        return new GetPolygonsResource([
          'data' => $polygonsIdsByStatus,
          'centroids' => $centroids,
        ]);
    }
}

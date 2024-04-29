<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\GetPolygonsResource;
use App\Models\V2\PolygonGeometry;
use Illuminate\Http\Request;
use App\Helpers\GeometryHelper;
use Illuminate\Support\Facades\Log;

class GetPolygonsController extends Controller
{
    public function getPolygonsOfProject(Request $request): GetPolygonsResource
    {
        $polygonsIds = TerrafundDashboardQueryHelper::getPolygonIdsOfProject($request);
        $polygons = PolygonGeometry::whereIn('uuid', $polygonsIds)->pluck('uuid');

        return new GetPolygonsResource([
          'data' => $polygons,
        ]);
    }

    public function getBboxOfCompleteProject(Request $request)
    {
        try {
            $polygonsIds = TerrafundDashboardQueryHelper::getPolygonIdsOfProject($request);
            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsIds);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }
};

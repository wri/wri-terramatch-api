<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\GeometryHelper;
use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\GetPolygonsResource;
use App\Models\V2\PolygonGeometry;
use Illuminate\Http\Request;
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

    public function getPolygonsByStatusOfProject(Request $request): GetPolygonsResource
    {
        $polygonsIds = TerrafundDashboardQueryHelper::getPolygonsByStatusOfProject($request);

        return new GetPolygonsResource([
          'data' => $polygonsIds,
        ]);
    }

    public function getPolygonsUuidsByStatusForProject(Request $request): GetPolygonsResource
    {
        $polygonsIds = TerrafundDashboardQueryHelper::getPolygonsUuidsByStatusForProject($request);

        return new GetPolygonsResource([
          'data' => $polygonsIds,
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

    public function getProjectBbox(Request $request)
    {
        try {
            $polygonsIds = TerrafundDashboardQueryHelper::getPolygonUuidsOfProject($request);
            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsIds);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }
};

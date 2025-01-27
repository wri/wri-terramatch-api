<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\GeometryHelper;
use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\GetPolygonsResource;
use App\Models\LandscapeGeom;
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

    public function getPolygonsDataByStatusOfProject(Request $request): GetPolygonsResource
    {
        $polygonsIds = TerrafundDashboardQueryHelper::getPolygonsByStatusOfProjects($request);

        return new GetPolygonsResource([
          'data' => $polygonsIds,
        ]);
    }

    public function getBboxOfCompleteProject(Request $request)
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
    public function getLandscapeBbox(Request $request)
    {
        $landscape = $request->input('landscape');
    
        $envelopes = LandscapeGeom::where('landscape', $landscape)
            ->selectRaw('ST_AsGeoJSON(ST_Envelope(geometry)) as envelope')
            ->get();
    
        if ($envelopes->isEmpty()) {
            return null;
        }
    
        $maxX = $maxY = PHP_INT_MIN;
        $minX = $minY = PHP_INT_MAX;
    
        foreach ($envelopes as $envelope) {
            $geojson = json_decode($envelope->envelope);
            $coordinates = $geojson->coordinates[0];
    
            foreach ($coordinates as $point) {
                $x = $point[0];
                $y = $point[1];
                $maxX = max($maxX, $x);
                $minX = min($minX, $x);
                $maxY = max($maxY, $y);
                $minY = min($minY, $y);
            }
        }
    
        return [
            'bbox' => [$minX, $minY, $maxX, $maxY],
            'landscape' => $landscape
        ];
    }
};

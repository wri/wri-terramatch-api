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

      // Fetch the ST_Envelope of each geometry as GeoJSON
      $envelopes = PolygonGeometry::whereIn('uuid', $polygonsIds)
        ->selectRaw('ST_ASGEOJSON(ST_Envelope(geom)) as envelope')
        ->get();
  
      // Initialize variables for maximum and minimum coordinates
      $maxX = $maxY = PHP_INT_MIN;
      $minX = $minY = PHP_INT_MAX;
  
      // Iterate through each envelope to extract bounding box coordinates
      foreach ($envelopes as $envelope) {
        $geojson = json_decode($envelope->envelope);
        $coordinates = $geojson->coordinates[0]; // Get the exterior ring coordinates
  
        // Update maximum and minimum coordinates
        foreach ($coordinates as $point) {
          $x = $point[0];
          $y = $point[1];
          $maxX = max($maxX, $x);
          $minX = min($minX, $x);
          $maxY = max($maxY, $y);
          $minY = min($minY, $y);
        }
      }
  
      // Construct the bounding box coordinates
      $bboxCoordinates = [$minX, $minY, $maxX, $maxY];
  
      return response()->json(['bbox' => $bboxCoordinates]);
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
    }  
  }
};

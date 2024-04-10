<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\V2\PolygonGeometry;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\WorldCountryGeneralized;
use App\Models\V2\Sites\SitePolygon;

class TerrafundEditGeometryController extends Controller
{

  public function getSitePolygonData(string $uuid)
  {
      try {
          $sitePolygon = SitePolygon::where('poly_id', $uuid)->first();
  
          if (!$sitePolygon) {
              return response()->json(['message' => 'No site polygons found for the given UUID.'], 404);
          }

          return response()->json(['site_polygon' => $sitePolygon]);
      } catch (\Exception $e) {
          return response()->json(['message' => $e->getMessage()], 500);
      }
  }
  public function updateGeometry(string $uuid, Request $request)
  {
    $sitePolygon = SitePolygon::where('uuid', $uuid)->first();
    if (!$sitePolygon) {
      return response()->json(['error' => 'Site polygon not found'], 200);
    }
    $sitePolygon->update($request->all());
    return response()->json(['site_polygon' => $sitePolygon], 200);
  }
  public function getPolygonGeojson(string $uuid)
  {
    // get the st_geojson from polygon_geometry
    $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
    if (!$polygonGeometry) {
      return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
    }
    $geojson = DB::select("SELECT ST_AsGeoJSON(geom) as geojson FROM polygon_geometry WHERE uuid = '$uuid'");
    $geojsonData = json_decode($geojson[0]->geojson, true);
    return response()->json([
        'geojson' => $geojsonData
    ]);
  }
}

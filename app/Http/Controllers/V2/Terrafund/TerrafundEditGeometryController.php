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
    $geometry = json_decode($request->input('geometry'));
    $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");
    $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
    if (!$polygonGeometry) {
      return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
    }
    $polygonGeometry->geom = $geom;
    $polygonGeometry->save();
    return response()->json(['message' => 'Geometry updated successfully.', 'geometry' => $geometry, 'uuid' => $uuid]);
  }
  public function getPolygonGeojson(string $uuid)
  {
    // get the st_geojson from polygon_geometry
    $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
    if (!$polygonGeometry) {
      return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
    }
    $geojson = DB::table('polygon_geometry')
    ->select(DB::raw('ST_AsGeoJSON(geom) as geojson'))
    ->where('uuid', '=', $uuid)
    ->get();

    $geojsonData = json_decode($geojson[0]->geojson, true);
    return response()->json([
        'geojson' => $geojsonData
    ]);
  }
  public function updateSitePolygon(string $uuid, Request $request) {
    try {
      $sitePolygon = SitePolygon::where('uuid', $uuid)->first();
      if (!$sitePolygon) {
        return response()->json(['message' => 'No site polygons found for the given UUID.'], 404);
      }
      $validatedData = $request->validate([
        'poly_name' => 'nullable|string',
        'plantstart' => 'nullable|date',
        'plantend' => 'nullable|date',
        'practice' => 'nullable|string',
        'distr' => 'nullable|string',
        'num_trees' => 'nullable|integer',
        'est_area' => 'nullable|numeric',
        'target_sys' => 'nullable|string',
      ]);

      $sitePolygon->update($validatedData);
      return response()->json(['message' => 'Site polygon updated successfully'], 200);
    } catch (\Exception $e) {
        // Handle other exceptions
        return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }
  public function createSitePolygon(string $uuid, Request $request) {
    try {
        $validatedData = $request->validate([
            'poly_name' => 'nullable|string',
            'plantstart' => 'nullable|date',
            'plantend' => 'nullable|date',
            'practice' => 'nullable|string',
            'distr' => 'nullable|string',
            'num_trees' => 'nullable|integer',
            'target_sys' => 'nullable|string',
        ]);

        // Get the geometry from the polygon_geometry table using the UUID
        $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
        if (!$polygonGeometry) {
            return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
        }
        $geojson = $polygonGeometry->geometry;
        $areaSqDegrees = DB::selectOne("SELECT ST_Area(ST_GeomFromGeoJSON('$geojson')) AS area")->area;
        $latitude = DB::selectOne("SELECT ST_Y(ST_Centroid(ST_GeomFromGeoJSON('$geojson'))) AS latitude")->latitude;
        $areaSqMeters = $areaSqDegrees * pow(111320 * cos(deg2rad($latitude)), 2);
        $areaHectares = $areaSqMeters / 10000;
        $sitePolygon = new SitePolygon([
            'poly_name' => $validatedData['poly_name'],
            'plantstart' => $validatedData['plantstart'],
            'plantend' => $validatedData['plantend'],
            'practice' => $validatedData['practice'],
            'distr' => $validatedData['distr'],
            'num_trees' => $validatedData['num_trees'],
            'est_area' => $areaHectares, // Assign the calculated area
            'target_sys' => $validatedData['target_sys'],
        ]);
        $sitePolygon->poly_id = $uuid;
        $sitePolygon->uuid = Str::uuid();
        $sitePolygon->save();

        return response()->json(['message' => 'Site polygon created successfully', 'uuid' => $sitePolygon], 201);
    } catch (\Exception $e) {
        // Handle other exceptions
        return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

}

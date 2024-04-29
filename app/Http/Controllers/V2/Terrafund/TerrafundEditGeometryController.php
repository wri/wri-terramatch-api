<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

  public function updateEstAreainSitePolygon($polygonGeometry, $geometry)
  {
    try {
      $sitePolygon = SitePolygon::where('poly_id', $polygonGeometry->uuid)->first();

      if ($sitePolygon) {
        $geojson = json_encode($geometry);
        $areaSqDegrees = DB::selectOne("SELECT ST_Area(ST_GeomFromGeoJSON('$geojson')) AS area")->area;
        $latitude = DB::selectOne("SELECT ST_Y(ST_Centroid(ST_GeomFromGeoJSON('$geojson'))) AS latitude")->latitude;
        $unitLatitude = 111320;
        $areaSqMeters = $areaSqDegrees * pow($unitLatitude * cos(deg2rad($latitude)), 2);
        $areaHectares = $areaSqMeters / 10000;

        $sitePolygon->est_area = $areaHectares;
        $sitePolygon->save();

        Log::info("Updated area for site polygon with UUID: $sitePolygon->uuid");
      } else {
        Log::warning("Updating Area: Site polygon with UUID $polygonGeometry->uuid not found.");
      }
    } catch (\Exception $e) {
      Log::error("Error updating area in site polygon: " . $e->getMessage());
    }
  }
    public function updateProjectCentroid($polygonGeometry)
    {
        try {
            $sitePolygon = SitePolygon::where('poly_id', $polygonGeometry->uuid)->first();

            if ($sitePolygon) {
                $project = Project::where('uuid', $sitePolygon->project_id)->first();

                if ($project) {
                    $geometryHelper = new GeometryHelper();
                    $centroid = $geometryHelper->centroidOfProject($project->uuid);

                    if ($centroid === null) {
                        Log::warning("Invalid centroid for project UUID: $project->uuid");
                    }
                } else {
                    Log::warning("Project with UUID $sitePolygon->project_id not found.");
                }
            } else {
                Log::warning("Site polygon with UUID $polygonGeometry->uuid not found.");
            }
        } catch (\Exception $e) {
            Log::error('Error updating project centroid: ' . $e->getMessage());
        }
    }

  public function deletePolygonAndSitePolygon(string $uuid)
  {
      try {
          $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
          if (!$polygonGeometry) {
              return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
          }
          $sitePolygon = SitePolygon::where('poly_id', $uuid)->first();
          $projectUuid = $sitePolygon->project_id;
          if ($sitePolygon) {
              Log::info("Deleting associated site polygon for UUID: $uuid");
              $sitePolygon->delete();
          }
          $geometryHelper = new GeometryHelper();
          $geometryHelper->updateProjectCentroid($projectUuid);
          $polygonGeometry->delete();
          Log::info("Polygon geometry and associated site polygon deleted successfully for UUID: $uuid");
          return response()->json(['message' => 'Polygon geometry and associated site polygon deleted successfully.', 'uuid' => $uuid]);
      } catch (\Exception $e) {
          Log::error("An error occurred: " . $e->getMessage());
          // Return error response if an exception occurs
          return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
      }
  }
  

  public function updateGeometry(string $uuid, Request $request)
  {
    try {
      Log::info("Updating geometry for polygon with UUID: $uuid");

      $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
      if (!$polygonGeometry) {
        return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
      }
      $geometry = json_decode($request->input('geometry'));
      $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");
      $polygonGeometry->geom = $geom;
      $polygonGeometry->save();
      Log::info('ABOUT TO CREATE');
      $this->updateEstAreainSitePolygon($polygonGeometry, $geometry);
      $this->updateProjectCentroid($polygonGeometry);

      return response()->json(['message' => 'Geometry updated successfully.', 'geometry' => $geometry, 'uuid' => $uuid]);
    } catch (\Exception $e) {
      return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

  public function getPolygonGeojson(string $uuid)
  {
    $geometryQuery = PolygonGeometry::isUuid($uuid);
    if (!$geometryQuery->exists()) {
      return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
    }
    $geojsonData = json_decode($geometryQuery->select(DB::raw('ST_AsGeoJSON(geom) as geojson'))->first()->geojson, true);

    return response()->json([
      'geojson' => $geojsonData,
    ]);
  }

  public function updateSitePolygon(string $uuid, Request $request)
  {
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

  public function createSitePolygon(string $uuid, Request $request)
  {
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

      $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
      if (!$polygonGeometry) {
        return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
      }
      $areaSqDegrees = DB::selectOne('SELECT ST_Area(geom) AS area FROM polygon_geometry WHERE uuid = :uuid', ['uuid' => $uuid])->area;
      $latitude = DB::selectOne('SELECT ST_Y(ST_Centroid(geom)) AS latitude FROM polygon_geometry WHERE uuid = :uuid', ['uuid' => $uuid])->latitude;
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

      return response()->json(['message' => 'Site polygon created successfully', 'uuid' => $sitePolygon, 'area' => $areaHectares], 201);
    } catch (\Exception $e) {
      // Handle other exceptions
      return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
  }

    public function getPolygonBbox(string $uuid)
    {
        try {
            $bboxCoordinates = GeometryHelper::getPolygonsBbox([$uuid]);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }
}

<?php

namespace App\Helpers;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateVersionPolygonGeometryHelper
{
    /**
     * This method creates a polygon geometry from a collection of coordinates.
     */
    public static function createVersionPolygonGeometry(string $uuid, $geometry)
    {
        try {
            Log::info("Creating geometry version for polygon with UUID: $uuid");

            if ($geometry instanceof Request) {
                $geometry = $geometry->input('geometry');
            }

            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();

            if (! $polygonGeometry) {
                return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
            }

            $geometry = json_decode($geometry);
            $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");

            $sitePolygon = SitePolygon::where('poly_id', $polygonGeometry->uuid)->first();

            $user = Auth::user();

            $newGeometryVersion = PolygonGeometry::create([
                'geom' => $geom,
                'created_by' => $user->id,
            ]);
            $newPolygonVersion = $sitePolygon->createCopy($user, $newGeometryVersion->uuid, false);

            if ($newPolygonVersion) {
                self::updateEstAreainSitePolygon($newGeometryVersion, $geometry);
                self::updateProjectCentroidFromPolygon($newGeometryVersion);
                $newPolygonVersion->changeStatusOnEdit();
            }

            return response()->json(['message' => 'Site polygon version created successfully.', 'geometry' => $geometry, 'uuid' => $uuid], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public static function updateEstAreainSitePolygon($polygonGeometry, $geometry)
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

                $sitePolygon->calc_area = $areaHectares;
                $sitePolygon->save();

                Log::info("Updated area for site polygon with UUID: $sitePolygon->uuid");
            } else {
                Log::warning("Updating Area: Site polygon with UUID $polygonGeometry->uuid not found.");
            }
        } catch (\Exception $e) {
            Log::error('Error updating area in site polygon: ' . $e->getMessage());
        }
    }

    public static function updateProjectCentroidFromPolygon($polygonGeometry)
    {
        try {
            $sitePolygon = SitePolygon::where('poly_id', $polygonGeometry->uuid)->first();

            if ($sitePolygon) {
                $relatedSite = Site::where('uuid', $sitePolygon->site_id)->first();
                $project = Project::where('id', $relatedSite->project_id)->first();

                if ($project) {
                    $geometryHelper = new GeometryHelper();
                    $geometryHelper->updateProjectCentroid($project->uuid);

                } else {
                    Log::warning("Project with UUID $relatedSite->project_id not found.");
                }
            } else {
                Log::warning("Site polygon with UUID $polygonGeometry->uuid not found.");
            }
        } catch (\Exception $e) {
            Log::error('Error updating project centroid: ' . $e->getMessage());
        }
    }
}

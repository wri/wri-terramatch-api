<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolygonGeometryHelper
{
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

    public static function getPolygonsWithNames(array $uuids)
    {
        $sitePolygons = SitePolygon::whereIn('poly_id', $uuids)
                        ->get(['poly_id', 'poly_name'])
                        ->toArray();

        return $sitePolygons;
    }
}

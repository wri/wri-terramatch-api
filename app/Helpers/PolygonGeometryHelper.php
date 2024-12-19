<?php

namespace App\Helpers;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Services\AreaCalculationService;
use Illuminate\Support\Facades\Log;

class PolygonGeometryHelper
{
    public static function updateEstAreainSitePolygon($polygonGeometry, $geometry)
    {
        try {
            $sitePolygon = SitePolygon::where('poly_id', $polygonGeometry->uuid)->first();

            if ($sitePolygon) {
                $areaCalculationService = app(AreaCalculationService::class);
                $areaHectares = $areaCalculationService->getArea((array) $geometry->geometry);

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

    public static function getPolygonsProjection(array $uuids, array $fields)
    {
        $sitePolygons = SitePolygon::whereIn('poly_id', $uuids)
                        ->where('is_active', 1)
                        ->get($fields)
                        ->toArray();

        return $sitePolygons;
    }
}

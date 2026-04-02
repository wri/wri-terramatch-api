<?php

namespace App\Helpers;

use App\Constants\PolygonFields;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeometryHelper
{
    public static function generateGeoJSON($project = null, $siteUuid = null)
    {
        $query = SitePolygon::query();

        if ($project) {
            $query->whereHas('site', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            });
        }

        if ($siteUuid) {
            $query->where('site_id', $siteUuid);
        }

        $sitePolygons = $query->active()->get();

        $features = [];
        foreach ($sitePolygons as $sitePolygon) {
            $polygonGeometry = PolygonGeometry::where('uuid', $sitePolygon->poly_id)
                ->select(DB::raw('ST_AsGeoJSON(geom) AS geojsonGeom'))
                ->first();

            if (! $polygonGeometry) {
                throw new \Exception('No polygon geometry found for the given UUID.');
            }

            $fieldsToValidate = PolygonFields::EXTENDED_FIELDS;
            $sitePolygonExtraAttributes = $sitePolygon->sitePolygonData;
            $properties = [];
            foreach ($fieldsToValidate as $field) {
                $properties[$field] = $sitePolygon->$field;
            }

            if ($sitePolygonExtraAttributes !== null) {
                $extraData = $sitePolygonExtraAttributes->data;

                if (is_string($extraData)) {
                    $decoded = json_decode($extraData, true);
                    if (is_array($decoded)) {
                        $properties = array_merge($properties, $decoded);
                    }
                } elseif (is_array($extraData)) {
                    $properties = array_merge($properties, $extraData);
                }
            } else {
                Log::info("No related sitePolygonData found for sitePolygon with UUID: {$sitePolygon->uuid}");
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => json_decode($polygonGeometry->geojsonGeom),
                'properties' => $properties,
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    public static function updateSitePolygonCentroid(SitePolygon $sitePolygon): bool
    {
        if (! $sitePolygon->poly_id) {
            return false;
        }

        $centroid = PolygonGeometry::selectRaw('ST_X(ST_Centroid(geom)) AS `long`, ST_Y(ST_Centroid(geom)) AS lat')
            ->where('uuid', $sitePolygon->poly_id)
            ->first();

        if (! $centroid) {
            return false;
        }

        DB::table('site_polygon')
            ->where('id', $sitePolygon->id)
            ->update([
                'lat' => $centroid->lat,
                'long' => $centroid->long,
            ]);

        // Update the model instance in memory so it has the latest values
        $sitePolygon->lat = $centroid->lat;
        $sitePolygon->long = $centroid->long;

        return true;
    }
}

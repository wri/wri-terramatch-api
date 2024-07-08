<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\WorldCountryGeneralized;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

class WithinCountry extends Extension
{
    public static $name = 'within_country';

    public static $message = [
        'key' => 'WITHIN_COUNTRY',
        'message' => 'The geometry must be within the project\'s assigned country',
    ];

    public const THRESHOLD_PERCENTAGE = 75;

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return self::getIntersectionData($value)['valid'];
    }

    public static function getIntersectionData(string $polygonUuid): array
    {
        if (empty($polygonUuid)) {
            return ['valid' => false, 'status' => 404, 'error' => 'UUID not provided'];
        }

        $geometry = PolygonGeometry::isUuid($polygonUuid)->first();
        if ($geometry === null) {
            return ['valid' => false, 'status' => 404, 'error' => 'Geometry not found'];
        }
        if ($geometry->db_geometry->area == 0) {
            return ['valid' => false, 'status' => 500, 'error' => 'Geometry invalid'];
        }

        $sitePolygonData = SitePolygon::forPolygonGeometry($polygonUuid)->select('site_id')->first();
        if ($sitePolygonData == null) {
            return ['valid' => false, 'status' => 404, 'error' => 'Site polygon data not found for the specified polygonUuid'];
        }

        $countryIso = $sitePolygonData->project->country;
        if ($countryIso == null) {
            return ['valid' => false, 'status' => 404, 'error' => 'Country ISO not found for the specified project'];
        }

        $intersectionData = WorldCountryGeneralized::forIso($countryIso)
            ->selectRaw(
                'world_countries_generalized.country AS country, 
                ST_Area(
                    ST_Intersection(
                        world_countries_generalized.geometry, 
                        (SELECT geom FROM polygon_geometry WHERE uuid = ?)
                    )
                ) AS area',
                [$polygonUuid]
            )
            ->first();

        $totalArea = $geometry->db_geometry->area;
        $insidePercentage = $intersectionData->area / $totalArea * 100;

        return [
            'valid' => $insidePercentage >= self::THRESHOLD_PERCENTAGE,
            'geometry_id' => $geometry->id,
            'inside_percentage' => $insidePercentage,
            'country_name' => $intersectionData->country,
        ];
    }

    public static function getIntersectionDataWithSiteId($geojson, $siteId): array
    {
        $siteData = Site::isUuid($siteId)->first();
        if (!$siteData) {
            return ['valid' => false, 'status' => 404, 'error' => 'Site data not found for the specified site_id'];
        }
        $project = $siteData->project;
        $countryIso = $project->country;

        if ($countryIso == null) {
            return ['valid' => false, 'status' => 404, 'error' => 'Country ISO not found for the specified project'];
        }

        $polygonArea = DB::selectOne("SELECT ST_Area(ST_GeomFromGeoJSON('$geojson')) AS area")->area;

        $intersectionData = WorldCountryGeneralized::forIso($countryIso)
            ->selectRaw(
                'world_countries_generalized.country AS country, 
                        ST_Area(
                            ST_Intersection(
                                world_countries_generalized.geometry, 
                                ST_GeomFromGeoJSON(?)
                            )
                        ) AS area',
                [$geojson]
            )
            ->first();

        if ($intersectionData === null) {
            return ['valid' => false, 'status' => 404, 'error' => 'No intersected data for specified project'];
        }

        $insidePercentage = $intersectionData->area / $polygonArea * 100;

        return [
            'valid' => $insidePercentage >= self::THRESHOLD_PERCENTAGE,
            'inside_percentage' => $insidePercentage,
            'country_name' => $intersectionData->country,
        ];
    }
}

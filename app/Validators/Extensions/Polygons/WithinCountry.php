<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\WorldCountryGeneralized;
use App\Validators\Extensions\Extension;

class WithinCountry extends Extension
{
    public static $name = 'within_country';

    public static $message = [
        'WITHIN_COUNTRY',
        'The {{attribute}} field must represent a polygon that is within its assigned country',
        ['attribute' => ':attribute'],
        'The :attribute field must represent a polygon that is within its assigned country',
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

        $sitePolygonData = SitePolygon::forPolygonGeometry($polygonUuid)->select('site_id')->first();
        if ($sitePolygonData == null) {
            return ['valid' => false, 'status' => 404, 'error' => 'Site polygon data not found for the specified polygonUuid'];
        }

        $countryIso = $sitePolygonData->project->country;
        if ($countryIso == null) {
            return ['valid' => false, 'status' => 404, 'error' => 'Country ISO not found for the specified project_id'];
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
}

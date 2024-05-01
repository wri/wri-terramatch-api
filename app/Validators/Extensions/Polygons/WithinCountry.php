<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\WorldCountryGeneralized;
use App\Validators\Extensions\Extension;

class WithinCountry extends Extension
{
    public static $name = 'has_polygon_site';

    public static $message = [
        'HAS_POLYGON_SITE',
        'The {{attribute}} field must represent a polygon with an attached site',
        ['attribute' => ':attribute'],
        'The :attribute field must represent a polygon with an attached site'
    ];

    public const THRESHOLD_PERCENTAGE = 75;

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $result = self::getIntersectionData($value);
        return $result != null && $result['valid'];
    }

    public static function getIntersectionData(string $polygonUuid): ?array
    {
        $geometry = PolygonGeometry::isUuid($polygonUuid)->first();
        $sitePolygonData = SitePolygon::forPolygonGeometry($polygonUuid)->select('id', 'project_id')->first();
        if ($geometry == null || $sitePolygonData == null) {
            return null;
        }

        $countryIso = $sitePolygonData->project->country;
        if ($countryIso == null) {
            return null;
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
            'inside_percentage' => $insidePercentage,
            'country_name' => $intersectionData->country
        ];
    }
}

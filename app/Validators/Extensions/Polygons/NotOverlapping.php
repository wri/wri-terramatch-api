<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;

class NotOverlapping extends Extension
{
    public static $name = 'not_overlapping';

    public static $message = [
        'HAS_POLYGON_SITE',
        'The {{attribute}} field must represent a polygon that does not overlap with other site polygons',
        ['attribute' => ':attribute'],
        'The :attribute field must represent a polygon that does not overlap with other site polygons',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $result = self::getIntersectionData($value);

        return $result != null && $result['valid'];
    }

    public static function getIntersectionData(string $polygonUuid): ?array
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if ($sitePolygon == null) {
            return null;
        }

        $relatedPolyIds = SitePolygon::where('project_id', $sitePolygon->project_id)
            ->where('poly_id', '!=', $polygonUuid)
            ->pluck('poly_id');

        $intersects = PolygonGeometry::whereIn('uuid', $relatedPolyIds)
            ->selectRaw(
                'ST_Intersects(
                    geom, 
                    (SELECT geom FROM polygon_geometry WHERE uuid = ?)
                ) as intersects',
                [$polygonUuid]
            )
            ->get()
            ->pluck('intersects');

        return [
            'valid' => ! in_array(1, $intersects->toArray()),
            'project_id' => $sitePolygon->project_id,
        ];
    }
}

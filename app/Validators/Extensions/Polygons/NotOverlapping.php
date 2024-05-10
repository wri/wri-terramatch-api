<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;

class NotOverlapping extends Extension
{
    public static $name = 'not_overlapping';

    public static $message = [
        'key' => 'OVERLAPPING_POLYGON',
        'message' => 'The geometry must not overlap with other project geometry',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return self::getIntersectionData($value)['valid'];
    }

    public static function getIntersectionData(string $polygonUuid): array
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if ($sitePolygon == null) {
            return ['valid' => false, 'error' => 'Site polygon not found for the given polygon ID', 'status' => 404];
        }

        $relatedPolyIds = $sitePolygon->project->sitePolygons()->whereNot('poly_id', $polygonUuid)->pluck('poly_id');
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
            'uuid' => $polygonUuid,
            'project_id' => $sitePolygon->project->id,
        ];
    }
}
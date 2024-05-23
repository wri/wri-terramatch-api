<?php

namespace App\Helpers;

use App\Models\V2\PolygonGeometry;

class GeometryHelper
{
    public static function getPolygonsBbox($polygonsIds)
    {
        $envelopes = PolygonGeometry::whereIn('uuid', $polygonsIds)
          ->selectRaw('ST_ASGEOJSON(ST_Envelope(geom)) as envelope')
          ->get();

        $maxX = $maxY = PHP_INT_MIN;
        $minX = $minY = PHP_INT_MAX;

        foreach ($envelopes as $envelope) {
            $geojson = json_decode($envelope->envelope);
            $coordinates = $geojson->coordinates[0];

            foreach ($coordinates as $point) {
                $x = $point[0];
                $y = $point[1];
                $maxX = max($maxX, $x);
                $minX = min($minX, $x);
                $maxY = max($maxY, $y);
                $minY = min($minY, $y);
            }
        }

        $bboxCoordinates = [$minX, $minY, $maxX, $maxY];

        return $bboxCoordinates;
    }
}
<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Validators\Extensions\Extension;

class Spikes extends Extension
{
    public static $name = 'polygon_spikes';

    public static $message = [
        'key' => 'SPIKE',
        'message' => 'The geometry must not have spikes',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (is_string($value)) {
            // assume we have a DB UUID
            return self::uuidValid($value);
        }

        // assume we have GeoJSON
        return self::geoJsonValid($value);
    }

    public static function uuidValid($uuid): bool
    {
        return self::geoJsonValid(PolygonGeometry::isUuid($uuid)->first()->geo_json);
    }

    public static function geoJsonValid($geojson): bool
    {
        return count(self::detectSpikes($geojson)) === 0;
    }

    public static function detectSpikes($geometry): array
    {
        $spikes = [];

        if ($geometry['type'] === 'Polygon' || $geometry['type'] === 'MultiPolygon') {
            $coordinates = $geometry['type'] === 'Polygon' ? $geometry['coordinates'][0] : $geometry['coordinates'][0][0]; // First ring of the polygon or the first polygon in the MultiPolygon
            $numVertices = count($coordinates);
            $totalDistance = 0;

            for ($i = 0; $i < $numVertices - 1; $i++) {
                $totalDistance += self::calculateDistance($coordinates[$i], $coordinates[$i + 1]);
            }

            for ($i = 0; $i < $numVertices - 1; $i++) {
                $distance1 = self::calculateDistance($coordinates[$i], $coordinates[($i + 1) % $numVertices]);
                $distance2 = self::calculateDistance($coordinates[($i + 1) % $numVertices], $coordinates[($i + 2) % $numVertices]);
                $combinedDistance = $distance1 + $distance2;

                if ($combinedDistance > 0.6 * $totalDistance) {
                    // Vertex and its adjacent vertices contribute more than 25% of the total boundary path distance
                    $spikes[] = $coordinates[($i + 1) % $numVertices];
                }
            }
        }

        return $spikes;
    }

    private static function calculateDistance($point1, $point2): float
    {
        $lat1 = $point1[1];
        $lon1 = $point1[0];
        $lat2 = $point2[1];
        $lon2 = $point2[0];

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return $miles * 1.609344;
    }
}

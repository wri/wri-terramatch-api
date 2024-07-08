<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

class PolygonSize extends Extension
{
    public static $name = 'polygon_size';

    public static $message = [
        'key' => 'SIZE_LIMIT',
        'message' => 'The geometry must not be larger than ' . self::SIZE_LIMIT . 'square kilometers',
    ];

    public const SIZE_LIMIT = 10000000;

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
        $areaSqMeters = self::calculateSqMeters(PolygonGeometry::isUuid($uuid)->first()->db_geometry);

        return $areaSqMeters <= self::SIZE_LIMIT;
    }

    public static function geoJsonValid($geojson): bool
    {
        $areaSqMeters = self::calculateSqMeters(DB::selectOne(
            'SELECT 
                ST_Area(params.geom) AS area, 
                ST_Y(ST_Centroid(params.geom)) AS latitude
            FROM (
                SELECT ST_GeomFromGeoJSON(:geojson) AS geom
            ) as params',
            ['geojson' => json_encode($geojson)]
        ));

        return $areaSqMeters <= self::SIZE_LIMIT;
    }

    public static function calculateSqMeters($dbGeometry): float
    {
        $areaSqDegrees = $dbGeometry->area;
        $latitude = $dbGeometry->latitude;

        return $areaSqDegrees * pow(111320 * cos(deg2rad($latitude)), 2);
    }
    public static function getArea(array $geometry): float
    {
        $type = $geometry['type'];
        $totalAreaSqMeters = 0;
    
        // Function to calculate area details from a given GeoJSON string
        $calculateArea = function($geojson) use (&$totalAreaSqMeters) {
            $result = DB::selectOne("
                SELECT 
                    ST_Area(ST_GeomFromGeoJSON(?)) AS area,
                    ST_Y(ST_Centroid(ST_GeomFromGeoJSON(?))) AS latitude
            ", [$geojson, $geojson]);
    
            $areaSqDegrees = $result->area;
            $latitude = $result->latitude;
    
            // Convert area to square meters
            $unitLatitude = 111320; // length of one degree of latitude in meters at the equator
            $areaSqMeters = $areaSqDegrees * pow($unitLatitude * cos(deg2rad($latitude)), 2);
    
            $totalAreaSqMeters += $areaSqMeters;
        };
    
        if ($type === 'Polygon') {
            // Convert single Polygon geometry to GeoJSON string
            $geojson = json_encode([
                'type' => 'Feature',
                'geometry' => $geometry,
                'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']]
            ]);
    
            $calculateArea($geojson);
        } elseif ($type === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $polygon) {
                // Convert each Polygon in MultiPolygon to GeoJSON string
                $geojson = json_encode([
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => $polygon
                    ],
                    'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']]
                ]);
    
                $calculateArea($geojson);
            }
        }
    
        // Convert total area to hectares
        return $totalAreaSqMeters / 10000;
    }
    
}

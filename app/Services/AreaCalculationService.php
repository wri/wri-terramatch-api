<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaCalculationService
{
    public function calculateArea(array $geometry): float
    {
        try {
            $geojson = json_encode($geometry);
            $result = DB::selectOne('
                SELECT 
                    ST_Area(ST_GeomFromGeoJSON(?)) * 
                    POW(6378137 * PI() / 180, 2) * 
                    COS(RADIANS(ST_Y(ST_Centroid(ST_GeomFromGeoJSON(?))))) / 10000 as area_hectares
            ', [$geojson, $geojson]);

            return (float) $result->area_hectares;

        } catch (\Exception $e) {
            Log::error('Error calculating area: ' . $e->getMessage());

            throw new \RuntimeException('Area calculation failed: ' . $e->getMessage());
        }
    }

    public function getGeomAndArea(array $geometry): array
    {
        try {
            $geojson = json_encode($geometry);
            $result = DB::selectOne('
                SELECT 
                    ? as geom_json,
                    ST_Area(
                        ST_GeomFromGeoJSON(?)
                    ) * 
                    POW(6378137 * PI() / 180, 2) * 
                    COS(RADIANS(ST_Y(ST_Centroid(ST_GeomFromGeoJSON(?))))) / 10000 as area_hectares
            ', [$geojson, $geojson, $geojson]);

            return [
                'geom' => DB::raw("ST_GeomFromGeoJSON('" . addslashes($geojson) . "')"),
                'area' => (float) $result->area_hectares,
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating geometry and area: ' . $e->getMessage());

            throw new \RuntimeException('Geometry and area calculation failed: ' . $e->getMessage());
        }
    }

    public function batchGetGeomsAndAreas(array $geometries): array
    {
        if (empty($geometries)) {
            return [];
        }

        try {
            $placeholders = [];
            $params = [];
            $results = [];

            foreach ($geometries as $index => $geometry) {
                $geojson = json_encode($geometry);
                $placeholders[] = '
                    SELECT 
                        ? as geom_json,
                        ST_Area(
                            ST_GeomFromGeoJSON(?)
                        ) * 
                        POW(6378137 * PI() / 180, 2) * 
                        COS(RADIANS(ST_Y(ST_Centroid(ST_GeomFromGeoJSON(?))))) / 10000 as area_hectares,
                        ? as batch_index
                ';
                $params = array_merge($params, [$geojson, $geojson, $geojson, $index]);
            }

            $sql = implode(' UNION ALL ', $placeholders);
            $dbResults = DB::select($sql, $params);

            foreach ($dbResults as $dbResult) {
                $index = $dbResult->batch_index;
                $geojson = $dbResult->geom_json;
                $results[$index] = [
                    'geom' => DB::raw("ST_GeomFromGeoJSON('" . addslashes($geojson) . "')"),
                    'area' => (float) $dbResult->area_hectares,
                ];
            }

            return $results;

        } catch (\Exception $e) {
            $results = [];
            foreach ($geometries as $index => $geometry) {
                $results[$index] = $this->getGeomAndArea($geometry);
            }

            return $results;
        }
    }
}

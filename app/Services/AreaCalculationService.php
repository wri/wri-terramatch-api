<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class AreaCalculationService
{
    protected function calculateArea(array $geometry): float
    {
        $geojson = json_encode([
            'type' => 'Feature',
            'geometry' => $geometry,
            'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']],
        ]);

        $inputGeojson = tempnam(sys_get_temp_dir(), 'input_') . '.geojson';
        $outputGeojson = tempnam(sys_get_temp_dir(), 'output_') . '.geojson';

        try {
            file_put_contents($inputGeojson, $geojson);

            $process = new Process([
                'python3',
                base_path() . '/resources/python/polygon-area/app.py',
                $inputGeojson,
                $outputGeojson,
            ]);

            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('Area calculation failed: ' . $process->getErrorOutput());

                throw new \RuntimeException('Area calculation failed: ' . $process->getErrorOutput());
            }

            $result = json_decode(file_get_contents($outputGeojson), true);

            return $result['area_hectares'];

        } catch (\Exception $e) {
            Log::error('Error calculating area: ' . $e->getMessage());

            throw $e;
        } finally {
            @unlink($inputGeojson);
            @unlink($outputGeojson);
        }
    }

    public function getGeomAndArea(array $geometry): array
    {
        $geojson = json_encode([
            'type' => 'Feature',
            'geometry' => $geometry,
            'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']],
        ]);

        $geom = DB::raw("ST_GeomFromGeoJSON('$geojson')");
        $areaHectares = $this->calculateArea($geometry);

        return ['geom' => $geom, 'area' => $areaHectares];
    }

    public function getArea(array $geometry): float
    {
        if ($geometry['type'] === 'MultiPolygon') {
            $totalArea = 0;
            foreach ($geometry['coordinates'] as $polygon) {
                $polygonGeometry = [
                    'type' => 'Polygon',
                    'coordinates' => $polygon,
                ];
                $totalArea += $this->calculateArea($polygonGeometry);
            }

            return $totalArea;
        }

        return $this->calculateArea($geometry);
    }
}

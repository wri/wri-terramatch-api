<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

class DuplicateGeometry extends Extension
{
    public static $name = 'duplicate_geometry';

    public static $message = [
        'key' => 'DUPLICATE_GEOMETRY',
        'message' => 'The geometry already exists in the project',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return self::getDuplicateData($value)['valid'];
    }

    public static function getDuplicateData(string $polygonUuid): array
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if (! $sitePolygon) {
            return ['valid' => false, 'error' => 'Site polygon not found', 'status' => 404];
        }

        $relatedPolyIds = $sitePolygon->project->sitePolygons()
            ->where('poly_id', '!=', $polygonUuid)
            ->pluck('poly_id');

        if ($relatedPolyIds->isEmpty()) {
            return ['valid' => true, 'uuid' => $polygonUuid, 'project_id' => $sitePolygon->project_id];
        }

        $bboxFilteredPolyIds = PolygonGeometry::join('site_polygon', 'polygon_geometry.uuid', '=', 'site_polygon.poly_id')
            ->whereIn('polygon_geometry.uuid', $relatedPolyIds)
            ->whereRaw('ST_Intersects(ST_Envelope(polygon_geometry.geom), (SELECT ST_Envelope(geom) FROM polygon_geometry WHERE uuid = ?))', [$polygonUuid])
            ->pluck('polygon_geometry.uuid');

        if ($bboxFilteredPolyIds->isEmpty()) {
            return ['valid' => true, 'uuid' => $polygonUuid, 'project_id' => $sitePolygon->project_id];
        }

        $duplicates = PolygonGeometry::join('site_polygon', 'polygon_geometry.uuid', '=', 'site_polygon.poly_id')
            ->whereIn('polygon_geometry.uuid', $bboxFilteredPolyIds)
            ->whereRaw('ST_Equals(polygon_geometry.geom, (SELECT geom FROM polygon_geometry WHERE uuid = ?))', [$polygonUuid])
            ->select([
                'polygon_geometry.uuid',
                'site_polygon.poly_name',
            ])
            ->get();

        $duplicateInfo = $duplicates->map(function ($duplicate) use ($sitePolygon) {
            $siteInfo = SitePolygon::where('poly_id', $duplicate->uuid)->first();

            return [
                'poly_uuid' => $duplicate->uuid,
                'poly_name' => $duplicate->poly_name,
                'site_name' => $siteInfo->site->name,
            ];
        })->toArray();

        return [
            'valid' => empty($duplicateInfo),
            'uuid' => $polygonUuid,
            'project_id' => $sitePolygon->project_id,
            'duplicates' => $duplicateInfo,
        ];
    }

    public static function checkNewFeaturesDuplicates($geojsonFeatures, $siteId): array
    {
        if (! is_array($geojsonFeatures) || empty($geojsonFeatures)) {
            return ['valid' => true, 'duplicates' => []];
        }

        $sitePolygon = SitePolygon::where('site_id', $siteId)->first();
        if (! $sitePolygon) {
            return ['valid' => true, 'duplicates' => []];
        }

        $existingPolyIds = $sitePolygon->project->sitePolygons()->pluck('poly_id');
        if ($existingPolyIds->isEmpty()) {
            return ['valid' => true, 'duplicates' => []];
        }

        $duplicatePairs = self::bulkDuplicateCheck($geojsonFeatures, $existingPolyIds);

        return [
            'valid' => empty($duplicatePairs),
            'duplicates' => array_values($duplicatePairs),
            'site_id' => $siteId,
            'project_id' => $sitePolygon->project_id,
        ];
    }

    private static function bulkDuplicateCheck($geojsonFeatures, $existingPolyIds): array
    {
        if (empty($geojsonFeatures) || $existingPolyIds->isEmpty()) {
            return [];
        }

        $geometryParams = [];
        $indexMap = [];

        foreach ($geojsonFeatures as $index => $feature) {
            if (! isset($feature['geometry'])) {
                continue;
            }
            $geomJson = json_encode($feature['geometry']);
            $geometryParams[] = $geomJson;
            $indexMap[] = $index;
        }

        if (empty($geometryParams)) {
            return [];
        }

        $unionParts = [];
        $allParams = [];

        foreach ($geometryParams as $i => $geomJson) {
            $unionParts[] = "SELECT {$indexMap[$i]} as idx, ST_GeomFromGeoJSON(?) as geom";
            $allParams[] = $geomJson;
        }

        $existingPlaceholders = str_repeat('?,', $existingPolyIds->count() - 1) . '?';
        $allParams = array_merge($allParams, $existingPolyIds->toArray());

        $sql = '
            SELECT DISTINCT ng.idx, pg.uuid as existing_uuid
            FROM (
                ' . implode(' UNION ALL ', $unionParts) . "
            ) ng
            INNER JOIN polygon_geometry pg ON pg.uuid IN ({$existingPlaceholders})
            WHERE ST_Intersects(ST_Envelope(ng.geom), ST_Envelope(pg.geom))
            AND ST_Equals(ng.geom, pg.geom)
        ";

        try {
            $results = DB::select($sql, $allParams);

            return array_map(fn ($row) => [
                'index' => (int) $row->idx,
                'existing_uuid' => $row->existing_uuid,
            ], $results);
        } catch (\Exception $e) {
            // Emergency fallback: Process in smaller chunks
            return self::emergencyFallback($geojsonFeatures, $existingPolyIds);
        }
    }

    private static function emergencyFallback($geojsonFeatures, $existingPolyIds): array
    {
        $duplicatePairs = [];
        $existingPlaceholders = str_repeat('?,', $existingPolyIds->count() - 1) . '?';

        $batchSize = 20;
        for ($i = 0; $i < count($geojsonFeatures); $i += $batchSize) {
            $batch = array_slice($geojsonFeatures, $i, $batchSize, true);

            foreach ($batch as $index => $feature) {
                if (! isset($feature['geometry'])) {
                    continue;
                }

                $geomJson = json_encode($feature['geometry']);

                // Return one matching uuid if exists
                $match = DB::selectOne(
                    "SELECT pg.uuid as existing_uuid FROM polygon_geometry pg
                        WHERE pg.uuid IN ({$existingPlaceholders})
                        AND ST_Intersects(ST_Envelope(pg.geom), ST_Envelope(ST_GeomFromGeoJSON(?)))
                        AND ST_Equals(pg.geom, ST_GeomFromGeoJSON(?))
                        LIMIT 1",
                    array_merge($existingPolyIds->toArray(), [$geomJson, $geomJson])
                );

                if ($match && isset($match->existing_uuid)) {
                    $duplicatePairs[] = [
                        'index' => $index,
                        'existing_uuid' => $match->existing_uuid,
                    ];
                }
            }
        }

        return $duplicatePairs;
    }
}

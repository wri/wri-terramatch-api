<?php

namespace App\Helpers;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\CriteriaSite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeometryHelper
{
    public function centroidOfProject($projectUuid)
    {
        $project = Project::where('uuid', $projectUuid)->first();

        if (! $project) {
            return null;
        }
        $polyIds = $project->sitePolygons()->pluck('poly_id')->toArray();

        if (empty($polyIds)) {
            return null;
        }

        $centroids = PolygonGeometry::selectRaw('ST_AsGeoJSON(ST_Centroid(geom)) AS centroid')
          ->whereIn('uuid', $polyIds)
          ->get();

        if ($centroids->isEmpty()) {
            return null; // Return null if no centroids are found
        }

        $centroidCount = $centroids->count();
        $totalLatitude = 0;
        $totalLongitude = 0;

        foreach ($centroids as $centroid) {
            $centroidData = json_decode($centroid->centroid, true);
            $totalLatitude += $centroidData['coordinates'][1];
            $totalLongitude += $centroidData['coordinates'][0];
        }

        $averageLatitude = $totalLatitude / $centroidCount;
        $averageLongitude = $totalLongitude / $centroidCount;

        $centroidOfCentroids = json_encode([
          'type' => 'Point',
          'coordinates' => [$averageLongitude, $averageLatitude],
        ]);

        return $centroidOfCentroids;
    }

    public function updateProjectCentroid(string $projectUuid)
    {
        try {
            $centroid = $this->centroidOfProject($projectUuid);

            if ($centroid === null) {
                Log::warning("Invalid centroid for projectUuid: $projectUuid");
            }

            $centroidArray = json_decode($centroid, true);

            $latitude = $centroidArray['coordinates'][1];
            $longitude = $centroidArray['coordinates'][0];


            Project::where('uuid', $projectUuid)
                ->update([
                    'lat' => $latitude,
                    'long' => $longitude,
                ]);


            Log::info("Centroid updated for projectUuid: $projectUuid");

            return response()->json([
              'message' => 'Centroid updated',
              'centroid' => $centroid,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating centroid for projectUuid: $projectUuid");

            return response()->json([
              'message' => 'Error updating centroid',
              'error' => $e->getMessage(),
            ], 500);
        }

    }

    public static function getPolygonsBbox($polygonsIds)
    {
        if (count($polygonsIds) === 0) {
            return null;
        }
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

        return [$minX, $minY, $maxX, $maxY];
    }

    public static function getCriteriaDataForPolygonGeometry($polygonGeometry)
    {
        // Fetch data from criteria_site with distinct criteria_id based on the latest created_at
        $criteriaDataQuery = 'SELECT criteria_id, MAX(created_at) AS latest_created_at
                            FROM criteria_site 
                            WHERE polygon_id = ?
                            GROUP BY criteria_id';

        $criteriaData = DB::select($criteriaDataQuery, [$polygonGeometry->uuid]);

        // Determine the validity of each criteria
        $criteriaList = [];
        foreach ($criteriaData as $criteria) {
            $criteriaId = $criteria->criteria_id;
            $valid = CriteriaSite::where(['polygon_id' => $polygonGeometry->uuid, 'criteria_id' => $criteriaId])->orderBy('created_at', 'desc')->select('valid')->first()?->valid;
            $criteriaList[] = [
                'criteria_id' => $criteriaId,
                'latest_created_at' => $criteria->latest_created_at,
                'valid' => $valid,
            ];
        }

        return $criteriaList;
    }
}

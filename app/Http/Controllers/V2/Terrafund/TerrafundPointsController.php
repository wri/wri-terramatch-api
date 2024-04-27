<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDO;
use PDOException;

class TerrafundPointsController extends Controller
{
  public function calculateCentroidOfCentroids(string $projectUuid): string
  {
    $sitePolygons = SitePolygon::where('project_id', $projectUuid)->get();

    if ($sitePolygons->isEmpty()) {
      return null; // Return null if no polygons are found for the given projectUuid
    }

    $polyIds = $sitePolygons->pluck('poly_id')->toArray();

    $centroids = PolygonGeometry::selectRaw("ST_AsGeoJSON(ST_Centroid(geom)) AS centroid")
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
      'coordinates' => [$averageLongitude, $averageLatitude]
    ]);

    return $centroidOfCentroids;
  }
}

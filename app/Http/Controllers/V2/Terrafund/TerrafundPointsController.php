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
  public function calculateCentroidOfCentroids(string $projectUuid): string {
    // Retrieve the list of poly_id through the site_polygon table with the given projectUuid
    $sitePolygons = SitePolygon::where('project_id', $projectUuid)->get();
    $polyIds = $sitePolygons->pluck('poly_id')->toArray();

    // Create a placeholder string for the polyIds
    $placeholders = implode(',', array_fill(0, count($polyIds), '?'));

    // Bind the array values to the placeholders
    $centroid = PolygonGeometry::selectRaw("ST_AsGeoJSON(ST_Centroid(geom)) AS centroid")
        ->whereRaw("uuid IN ($placeholders)", $polyIds)
        ->value('centroid');

    return $centroid;
}

}
?>

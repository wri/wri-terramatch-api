<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;

class SitePolygonDataController extends Controller
{
    public function getSitePolygonData($site)
    {
        $sitePolygons = SitePolygon::where('site_id', $site)->get();
        Log::info(json_encode($sitePolygons));

        return $sitePolygons;
    }

    public function getBboxOfCompleteSite($site)
    {
        try {
            $sitePolygons = SitePolygon::where('site_id', $site)->get();
            $polygonsIds = $sitePolygons->pluck('poly_id');

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

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }
};

<?php

namespace App\Helpers;

use App\Helpers\PolygonGeometryHelper as PolyHelper;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateVersionPolygonGeometryHelper
{
    /**
     * This method creates a polygon geometry from a collection of coordinates.
     */
    public static function createVersionPolygonGeometry(string $uuid, $geometry)
    {
        try {
            Log::info("Creating geometry version for polygon with UUID: $uuid");

            if ($geometry instanceof Request) {
                $geometry = $geometry->input('geometry');
            }

            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();

            if (! $polygonGeometry) {
                return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
            }

            $geometry = json_decode($geometry);
            $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");

            $sitePolygon = SitePolygon::where('poly_id', $polygonGeometry->uuid)->first();

            $user = Auth::user();

            $newGeometryVersion = PolygonGeometry::create([
                'geom' => $geom,
                'created_by' => $user->id,
            ]);
            $newPolygonVersion = $sitePolygon->createCopy($user, $newGeometryVersion->uuid, false);

            if ($newPolygonVersion) {
                PolyHelper::updateEstAreainSitePolygon($newGeometryVersion, $geometry);
                PolyHelper::updateProjectCentroidFromPolygon($newGeometryVersion);
                $newPolygonVersion->changeStatusOnEdit();
            }

            return response()->json(['message' => 'Site polygon version created successfully.', 'geometry' => $geometry, 'uuid' => $uuid], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\GeometryHelper;
use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\GetPolygonsResource;
use App\Models\LandscapeGeom;
use App\Models\V2\Projects\Project;
use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetPolygonsController extends Controller
{
    public function getCentroidOfPolygon($polyUUID)
    {
        $centroid = GeometryHelper::centroidOfPolygon($polyUUID);

        return response()->json(['centroid' => $centroid]);
    }
    public function getPolygonsOfProject($projectId)
    {
        if (! $projectId) {
            return response()->json(['error' => 'Project ID is required'], 400);
        }
        $project = Project::whereUuid($projectId)->firstOrFail();
        $siteswithPolygons = $project->sites()
            ->with(['sitePolygons' => function ($query) {
                $query->select('uuid', 'site_id', 'poly_name', 'poly_id')
                     ->where('status', 'approved');
            }])
            ->select('uuid', 'name')
            ->get();

        return response()->json($siteswithPolygons);
    }

    public function getPolygonsDataByStatusOfProject(Request $request): GetPolygonsResource
    {
        $polygonsIds = TerrafundDashboardQueryHelper::getPolygonsByStatusOfProjects($request);

        return new GetPolygonsResource([
          'data' => $polygonsIds,
        ]);
    }

    public function getBboxOfCompleteProject(Request $request)
    {
        try {
            $polygonsIds = TerrafundDashboardQueryHelper::getPolygonUuidsOfProject($request);
            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsIds);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }

    public function getProjectBbox(Request $request)
    {
        try {
            $polygonsIds = TerrafundDashboardQueryHelper::getPolygonUuidsOfProject($request);
            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsIds);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }

    public function getLandscapeBbox(Request $request)
    {
        $landscapes = $request->input('landscapes');
        if ($landscapes === null) {
            return response()->json(['error' => 'Landscapes parameter is required'], 400);
        }
        if (is_string($landscapes)) {
            $landscapes = explode(',', $landscapes);
        }

        $envelopes = LandscapeGeom::whereIn('landscape', $landscapes)
            ->selectRaw('ST_AsGeoJSON(ST_Envelope(geometry)) as envelope, landscape')
            ->get();


        if ($envelopes->isEmpty()) {
            return null;
        }

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

        return [
            'bbox' => [$minX, $minY, $maxX, $maxY],
            'landscapes' => $landscapes,
        ];
    }

    public function getCountryLandscapeBbox(Request $request)
    {
        $landscapes = $request->input('landscapes');
        $iso = $request->input('country');

        $maxX = $maxY = PHP_INT_MIN;
        $minX = $minY = PHP_INT_MAX;

        if ($landscapes !== null) {
            if (is_string($landscapes)) {
                $landscapes = explode(',', $landscapes);
            }

            $envelopes = LandscapeGeom::whereIn('landscape', $landscapes)
                ->selectRaw('ST_AsGeoJSON(ST_Envelope(geometry)) as envelope, landscape')
                ->get();

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
        }

        if ($iso !== null) {
            $countryData = WorldCountryGeneralized::where('iso', $iso)
                ->selectRaw('ST_AsGeoJSON(ST_Envelope(geometry)) AS bbox, country')
                ->first();

            if (! $countryData) {
                return response()->json(['error' => 'Country not found'], 404);
            }

            $geoJson = json_decode($countryData->bbox);
            $coordinates = $geoJson->coordinates[0];

            foreach ($coordinates as $point) {
                $x = $point[0];
                $y = $point[1];
                $maxX = max($maxX, $x);
                $minX = min($minX, $x);
                $maxY = max($maxY, $y);
                $minY = min($minY, $y);
            }

            $countryName = $countryData->country;
        }

        if ($maxX === PHP_INT_MIN || $minX === PHP_INT_MAX) {
            return response()->json(['error' => 'No valid bounding box found'], 404);
        }

        $response = [
            'bbox' => [$minX, $minY, $maxX, $maxY],
        ];

        if (isset($landscapes)) {
            $response['landscapes'] = $landscapes;
        }

        if (isset($countryName)) {
            $response['country'] = $countryName;
        }

        return response()->json($response);
    }
};

<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreSitePolygonNewVersionController extends Controller
{
    public function __invoke(string $uuid): JsonResponse
    {
        Log::info('Fetching site polygons', ['uuid' => $uuid]);

        try {
            $sitePolygon = SitePolygon::isUuid($uuid)->first();
            if (! $sitePolygon) {
                return response()->json(['error' => 'Site polygon not found'], 404);
            }
            $user = User::isUuid(Auth::user()->uuid)->first();
            $newSitePolygon = $sitePolygon->createCopy($user);

            Log::info('Fetched site polygon', ['sitePolygon' => $sitePolygon]);

            return response()->json($newSitePolygon);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }
}

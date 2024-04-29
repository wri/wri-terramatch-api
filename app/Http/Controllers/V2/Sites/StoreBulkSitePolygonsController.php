<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreBulkSitePolygonsController extends Controller
{
    public function __invoke(Request $request, Site $site): JsonResponse
    {
        $this->authorize('uploadPolygons', $site);


    }
}

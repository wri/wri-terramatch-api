<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;

class SitePolygonDataController extends Controller
{
    public function __invoke($site)
    {
        $sitePolygons = SitePolygon::where('site_id', $site)->get();
        Log::info(json_encode($sitePolygons));

        return $sitePolygons;
    }
};

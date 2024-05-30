<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\V2SitePolygonsCollection;
use App\Models\V2\Sites\SitePolygon;

class AdminSiteIndexSitePolygonsController extends Controller
{
    public function __invoke(string $uuid): V2SitePolygonsCollection
    {
        $sitePolygons = SitePolygon::where('site_id', $uuid)->get();

        return new V2SitePolygonsCollection($sitePolygons);
    }
}

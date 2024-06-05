<?php

namespace App\Http\Controllers\V2\Polygons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V2\Sites\SitePolygon;
use App\Http\Resources\V2\PolygonsApprovedResource;

class GetPolygonsApprovedController extends Controller
{
    public function __invoke(Request $request, string $uuid = null)
    {
        $PolygonsCount = SitePolygon::selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count')
            ->where('site_id', $uuid)
            ->first();
        $checkApproved = !($PolygonsCount->total == $PolygonsCount->approved_count);
        return new PolygonsApprovedResource(["check_polygons" => $checkApproved]);
    }
}

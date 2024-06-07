<?php

namespace App\Http\Controllers\V2\Polygons;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\PolygonsApprovedResource;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;

class GetPolygonsApprovedController extends Controller
{
    public function __invoke(Request $request, string $uuid = null)
    {
        $nonApproved = SitePolygon::where('site_id', $uuid)
            ->where('status', '!=', 'approved')
            ->first();

        return new PolygonsApprovedResource(['check_polygons' => $nonApproved != null]);
    }
}
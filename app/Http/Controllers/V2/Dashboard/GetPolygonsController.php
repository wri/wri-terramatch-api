<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;

class GetPolygonsController extends Controller
{
    public function getCentroidOfPolygon(string $polyUuid)
    {
        $centroid = GeometryHelper::centroidOfPolygon($polyUuid);

        return response()->json(['centroid' => $centroid]);
    }
}

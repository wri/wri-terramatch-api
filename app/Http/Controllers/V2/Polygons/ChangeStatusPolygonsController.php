<?php

namespace App\Http\Controllers\V2\Polygons;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use App\Http\Requests\V2\Polygons\ChangeStatusPolygonsUpdateRequest;
use App\Models\Traits\SaveAuditStatusTrait;

class ChangeStatusPolygonsController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(ChangeStatusPolygonsUpdateRequest $request)
    {
        $body = $request->all();
        $polygons = $body['updatePolygons'];

        foreach ($polygons as $polygon) {
            $sitePolygon = SitePolygon::where('uuid', $polygon['uuid'])->first();
            $sitePolygon->status = $polygon['status'];
            $sitePolygon->save();

            $this->saveAuditStatus("polygon", $sitePolygon->uuid, $polygon['status'], $body['comment'], 'status');
        }

        return response()->json($polygons);
    }
}

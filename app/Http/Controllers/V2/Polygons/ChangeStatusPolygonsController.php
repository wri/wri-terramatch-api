<?php

namespace App\Http\Controllers\V2\Polygons;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Polygons\ChangeStatusPolygonsUpdateRequest;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\Sites\SitePolygon;

class ChangeStatusPolygonsController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(ChangeStatusPolygonsUpdateRequest $request)
    {
        $body = $request->all();
        $polygons = $body['updatePolygons'];

        foreach ($polygons as $polygon) {
            $sitePolygon = SitePolygon::where('uuid', $polygon['uuid'])->first();
            if (! $sitePolygon) {
                continue;
            }
            $sitePolygon->status = $polygon['status'];
            $sitePolygon->save();

            $this->saveAuditStatus('polygon', $sitePolygon['id'], $polygon['status'], $body['comment'], 'status');
        }

        return response()->json($polygons);
    }
}

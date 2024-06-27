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
        $polygonCollection = collect($body['updatePolygons']);

        $sitePolygons = SitePolygon::whereIn('uuid', $polygonCollection->map(fn ($p) => $p['uuid']))->get();

        $polygonsChanged = [];
        foreach ($sitePolygons as $sitePolygon) {
            $foundPolygon = $polygonCollection->first(fn ($p) => $p['uuid'] === $sitePolygon->uuid);

            if (! $foundPolygon) {
                continue;
            }
            $sitePolygon->status = $foundPolygon['status'];
            $sitePolygon->save();
            $polygonsChanged[] = $sitePolygon;
            $this->saveAuditStatus('polygon', $sitePolygon['id'], $sitePolygon['status'], $body['comment'], 'status');
        }

        return response()->json($polygonsChanged);
    }
}

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
        $updatePolygons = $body['updatePolygons'];

        $polygonUuids = collect($updatePolygons)->pluck('uuid')->toArray();
        $sitePolygons = SitePolygon::whereIn('uuid', $polygonUuids)->get();

        $changedPolygons = [];
        foreach ($sitePolygons as $sitePolygon) {
            $foundPolygon = collect($updatePolygons)->first(fn ($p) => $p['uuid'] === $sitePolygon->uuid);

            if (! $foundPolygon) {
                continue;
            }
            $sitePolygon->status = $foundPolygon['status'];
            $sitePolygon->save();
            $changedPolygons[] = $sitePolygon;
            $this->saveAuditStatus('polygon', $sitePolygon['id'], $sitePolygon['status'], $body['comment'], 'status');
        }

        return response()->json($changedPolygons);
    }
}

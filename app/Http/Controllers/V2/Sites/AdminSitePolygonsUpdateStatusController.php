<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use App\Models\Traits\SaveAuditStatusTrait;

class AdminSitePolygonsUpdateStatusController extends Controller
{
    use SaveAuditStatusTrait;
    public function __invoke(Request $request, string $uuid): SitePolygon
    {
        $site = SitePolygon::where('uuid', $uuid)->first();
        $body = $request->all();
        if (isset($body['status'])) {
            $site['status'] = $body['status'];
            $this->saveAuditStatus('SitePolygon', $site->uuid, $body['status'], $body['comment'], $body['type']);
        } else if (isset($body['is_active'])) {
            $this->saveAuditStatus('SitePolygon', $site->uuid, $site->status, $body['comment'], $body['type'], $body['is_active']);
        } else {
            $this->saveAuditStatus('SitePolygon', $site->uuid, $site->status, $body['comment'], $body['type']);
        }

        $site->update();

        return $site;
    }
}

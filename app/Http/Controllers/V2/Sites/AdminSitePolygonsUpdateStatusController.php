<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditStatus\AuditStatus;

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
            AuditStatus::where('entity_uuid', $site->uuid)
                ->where('type', $body['type'])
                ->update(['is_active' => false]);
            $this->saveAuditStatus('SitePolygon', $site->uuid, $site->status, $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        }

        $site->update();

        return $site;
    }
}

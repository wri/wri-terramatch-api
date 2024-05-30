<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;

class AdminSitesUpdateStatusController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(Request $request, string $uuid): Site
    {
        $site = Site::where('uuid', $uuid)->first();
        $body = $request->all();
        if (isset($body['status'])) {
            $site['status'] = $body['status'];
            $this->saveAuditStatus('Site', $site->uuid, $body['status'], $body['comment'], $body['type']);
        } elseif (isset($body['is_active'])) {
            AuditStatus::where('entity_uuid', $site->uuid)
                ->where('type', $body['type'])
                ->where('is_active', true)
                ->update(['is_active' => false]);
            $this->saveAuditStatus('Site', $site->uuid, $site->status, $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        }
        $site->update();

        return $site;
    }
}

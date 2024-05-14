<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;
use App\Models\Traits\SaveAuditStatusTrait;

class AdminSitesUpdateStatusController extends Controller
{
    use SaveAuditStatusTrait;
    public function __invoke(Request $request, string $uuid): Site
    {
        $site = Site::where('uuid', $uuid)->first();
        $body = $request->all();
        $site['status']= $body['status'];
        $site->update();

        $this->saveAuditStatus('Site', $site->uuid, $body['status'], $body['comment']);

        return $site;
    }
}

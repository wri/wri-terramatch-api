<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Sites\Site;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminSitesUpdateStatusController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request, string $uuid): Site
    {
        $site = Site::where('uuid', $uuid)->first();
        $body = $request->all();
        $site['status']= $body['status'];
        $site->update();

        AuditStatus::create([
            'entity' => $this->getModel($site),
            'entity_uuid' => $site->uuid,
            'status' => $body['status'],
            'comment' => $body['comment'],
            // 'attachment_url' => $body['attachment_url'],
            'date_created' => now(),
            'created_by' => Auth::user()->email_address,
        ]);

        return $site;
    }
}

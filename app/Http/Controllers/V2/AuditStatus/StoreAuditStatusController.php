<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\Traits\SaveAuditAttachmentTrait;
use App\Models\Traits\SaveAuditStatusTrait;
use Illuminate\Http\Request;

class StoreAuditStatusController extends Controller
{
    use SaveAuditStatusTrait;
    use SaveAuditAttachmentTrait;
    public function __invoke(Request $request, string $collection = null): AuditStatusResource
    {
        $body = $request->all();
        $auditStatusresponse = $this->saveAuditStatus($body['entity'], $body['entity_uuid'], $body['status'], $body['comment'], $body['type']);

        if ($request->file('file')) {
            foreach ($request->file('file') as $file) {
                $this->saveAuditAttachment($auditStatusresponse->id, $file->getClientOriginalName());
            }
        }

        return new AuditStatusResource($auditStatusresponse);
    }
}

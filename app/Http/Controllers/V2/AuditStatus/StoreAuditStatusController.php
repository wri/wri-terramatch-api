<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\Traits\SaveAuditStatusTrait;
use Illuminate\Http\Request;

class StoreAuditStatusController extends Controller
{
    use SaveAuditStatusTrait;
    public function __invoke(Request $request) : AuditStatusResource
    {
        $body = $request->all();
        $auditStatusresponse = $this->saveAuditStatus($body['entity'], $body['entity_uuid'], $body['status'], $body['comment'], $body['type']);
        return new AuditStatusResource($auditStatusresponse);
    }
}

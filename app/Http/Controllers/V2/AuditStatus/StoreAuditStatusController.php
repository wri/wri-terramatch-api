<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreAuditStatusRequest;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;

class StoreAuditStatusController extends Controller
{
    public function __invoke(StoreAuditStatusRequest $storeAuditStatusRequest): AuditStatusResource
    {
        $auditStatus = AuditStatus::create($storeAuditStatusRequest->all());

        return new AuditStatusResource($auditStatus);
    }
}

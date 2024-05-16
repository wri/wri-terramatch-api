<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreAuditStatusRequest;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Traits\SaveAuditStatusTrait;

class StoreAuditStatusController extends Controller
{
    use SaveAuditStatusTrait;
    public function __invoke(StoreAuditStatusRequest $storeAuditStatusRequest): AuditStatusResource
    {
        $user = JWTAuth::parseToken()->authenticate();

        $auditStatus = new AuditStatus($storeAuditStatusRequest->all());
        $auditStatus->created_by = $user->email_address;
        $auditStatus->date_created = now();
        $auditStatus->save();

        return new AuditStatusResource($auditStatus);
    }
}

<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreAuditStatusRequest;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreAuditStatusController extends Controller
{
    public function __invoke(StoreAuditStatusRequest $storeAuditStatusRequest): AuditStatusResource
    {
        $auditStatus = new AuditStatus($storeAuditStatusRequest->all());
        $auditStatus->date_created = now();
        $auditStatus->save();

        return new AuditStatusResource($auditStatus);
    }
}

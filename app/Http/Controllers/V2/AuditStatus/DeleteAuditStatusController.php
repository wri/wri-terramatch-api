<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;

class DeleteAuditStatusController extends Controller
{
    public function __invoke(AuditStatus $auditStatus, String $id): AuditStatusResource
    {
        AuditStatus::where('id', $id)->delete();

        return new AuditStatusResource($auditStatus);
    }
}

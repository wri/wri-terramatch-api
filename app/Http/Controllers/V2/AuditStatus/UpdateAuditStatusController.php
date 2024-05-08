<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateAuditStatusRequest;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;

class UpdateAuditStatusController extends Controller
{
    public function __invoke(UpdateAuditStatusRequest $updateAuditStatusRequest, String $id): AuditStatusResource
    {
        $validatedData = $updateAuditStatusRequest->validated();
        $auditStatus = AuditStatus::findOrFail($id);
        $auditStatus->update($validatedData);

        return new AuditStatusResource($auditStatus);
    }
}

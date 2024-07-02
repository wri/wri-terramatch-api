<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\AuditStatus\AuditStatusCreateRequest;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;

class StoreAuditStatusController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(AuditStatusCreateRequest $auditStatusCreateRequest, AuditableModel $auditable): AuditStatusResource
    {
        $body = $auditStatusCreateRequest->all();

        if ($body['type'] === 'change-request') {
            AuditStatus::where([
                ['auditable_id', $auditable->id],
                ['type', 'change-request'],
                ['is_active', true],
            ])->update(['is_active' => false]);
            $auditStatus = $this->saveAuditStatus(get_class($auditable), $auditable->id, $body['status'], $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        } else {
            $auditStatus = $this->saveAuditStatus(get_class($auditable), $auditable->id, $body['status'], $body['comment'], $body['type']);
        }
        $auditStatus->entity_name = $auditable->name;

        return new AuditStatusResource($auditStatus);
    }
}

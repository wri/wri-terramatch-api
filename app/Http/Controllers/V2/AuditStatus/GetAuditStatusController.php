<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;

class GetAuditStatusController extends Controller
{
    public function __invoke(string $id = null)
    {
        if ($id != null) {
            $auditStatus = AuditStatus::where('id', $id)->first();

            return new AuditStatusResource($auditStatus);
        } else {
            $auditStatus = AuditStatus::orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return AuditStatusResource::collection($auditStatus);
        }
    }
}

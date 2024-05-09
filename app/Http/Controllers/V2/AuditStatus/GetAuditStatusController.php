<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;
use Illuminate\Http\Request;

class GetAuditStatusController extends Controller
{
    public function __invoke(Request $request, string $id = null)
    {
        if ($id != null) {
            $auditStatus = AuditStatus::where('id', $id)->first();

            return new AuditStatusResource($auditStatus);
        } else if ($request->has('entity')) {
            $auditStatus = AuditStatus::where('entity_uuid', $request->input('entity'))
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return AuditStatusResource::collection($auditStatus);
        } else {
            $auditStatus = AuditStatus::orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return AuditStatusResource::collection($auditStatus);
        }
    }
}

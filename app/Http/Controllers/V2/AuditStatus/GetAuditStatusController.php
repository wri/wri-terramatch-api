<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditableModel;
use Illuminate\Http\Request;

class GetAuditStatusController extends Controller
{
    public function __invoke(Request $request, AuditableModel $auditable)
    {
        $auditStatuses = $auditable->auditStatuses()
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $auditStatuses->map(function ($auditStatus) use ($auditable) {
            $auditStatus->entity_name = $auditable->name;

            return $auditStatus;
        });

        return AuditStatusResource::collection($auditStatuses);
    }
}

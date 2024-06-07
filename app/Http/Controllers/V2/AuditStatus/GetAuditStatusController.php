<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\EntityModel;
use Illuminate\Http\Request;

class GetAuditStatusController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity)
    {
        $auditStatuses = $entity->auditStatuses()
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return AuditStatusResource::collection($auditStatuses);
    }
}

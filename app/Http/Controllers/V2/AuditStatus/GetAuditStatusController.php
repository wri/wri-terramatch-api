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

        foreach ($auditStatuses as $auditStatus) {
            $auditStatus->entity_name = $auditable->getAuditableNameAttribute();
        }

        $combinedData = $auditStatuses->concat($this->getAudits($auditable));

        $sortedData = $combinedData->sortByDesc(function ($item) {
            return $item->updated_at ?? $item->created_at;
        });

        return AuditStatusResource::collection($sortedData);
    }

    private function getAudits($auditable)
    {
        if (! method_exists($auditable, 'audits')) {
            return collect();
        }

        $audits = $auditable->audits()
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $audits;
    }
}

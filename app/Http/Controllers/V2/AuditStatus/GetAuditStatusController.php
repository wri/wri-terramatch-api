<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\DTOs\AuditStatusDTO;
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

        $list = $auditStatuses->map(function ($auditStatus) {
            return AuditStatusDTO::fromAuditStatus($auditStatus);
        });

        $combinedData = $list->concat($this->getAudits($auditable));

        $sortedData = $combinedData->sortByDesc(function ($item) {
            return $item->date_created;
        });

        return AuditStatusResource::collection($sortedData);
    }

    private function getAudits($auditable)
    {
        if (! method_exists($auditable, 'audits')) {
            return collect();
        }

        $audits = $auditable->audits()
        ->where(function ($query) {
            $query->where('created_at', '<', '2024-09-01')
                  ->orWhere('updated_at', '<', '2024-09-01');
        })
        ->orderByDesc('updated_at')
        ->orderByDesc('created_at')
        ->get();
        return $audits->map(function ($audit) {
            return AuditStatusDTO::fromAudits($audit);
        });
    }
}

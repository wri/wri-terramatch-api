<?php

namespace App\Http\Controllers\V2\Entities;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\AuditStatus\AuditStatusUpdateRequest;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;

class UpdateEntityStatusController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(AuditStatusUpdateRequest $request, AuditableModel $auditable)
    {
        $this->authorize('update', $auditable);

        if (! $this->canChangeStatus($auditable, $request->status)) {
            return response()->json(['message' => 'Cannot change status'], 400);
        }

        $body = $request->all();
        $status = $body['status'];

        $auditable->status = $status;
        $auditable->save();

        if (isset($body['status'])) {
            $this->saveAuditStatus(get_class($auditable), $auditable->id, $status, $body['comment'], $body['type']);
        } elseif (isset($body['is_active'])) {
            AuditStatus::where('auditable_id', $auditable->id)
                ->where('type', $body['type'])
                ->where('is_active', true)
                ->update(['is_active' => false]);
            $this->saveAuditStatus(get_class($auditable), $auditable->id, $status, $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        }

        return $auditable;
    }

    private function canChangeStatus($auditable, $status): bool
    {
        switch(get_class($auditable)) {
            case 'App\Models\V2\Sites\Site':
                return $this->canChangeSiteStatusTo($auditable, $status);
            case 'App\Models\V2\Sites\SitePolygon':
                return $this->canChangeSitePolygonStatusTo($auditable, $status);
            default:
                return true;
        }
    }

    private function canChangeSiteStatusTo($auditable, $status)
    {
        if ($status === 'approved') {
            return ! SitePolygon::where('site_id', $auditable->id)->where('status', 'approved')->exists();
        }

        return true;
    }

    private function canChangeSitePolygonStatusTo($sitePolygon, $status)
    {
        if ($status === 'approved') {
            $geometry = $sitePolygon->polygonGeometry()->first();

            if ($geometry === null) {
                return false;
            }

            $criteriaList = GeometryHelper::getCriteriaDataForPolygonGeometry($geometry);

            if (empty($criteriaList)) {
                return false;
            }

            $criteriaList = array_filter($criteriaList, function ($criteria) {
                return $criteria['criteria_id'] !== PolygonService::ESTIMATED_AREA_CRITERIA_ID;
            });

            $canApprove = true;
            foreach ($criteriaList as $criteria) {
                if (! $criteria['valid']) {
                    $canApprove = false;

                    break;
                }
            }

            return $canApprove;
        }

        return true;
    }
}

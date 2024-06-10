<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\AuditStatus\AuditStatusUpdateRequest;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\EntityModel;
use App\Models\V2\Sites\SitePolygon;

class UpdateEntityStatusController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(AuditStatusUpdateRequest $request, EntityModel $entity)
    {
        $this->authorize('update', $entity);


        if (! $this->canChangeStatus($entity, $request->status)) {
            return response()->json(['message' => 'Cannot change status'], 400);
        }

        $body = $request->all();
        $status = $body['status'];

        $entity->status = $status;
        $entity->save();

        if (isset($body['status'])) {
            $this->saveAuditStatus(get_class($entity), $entity->id, $status, $body['comment'], $body['type']);
        } elseif (isset($body['is_active'])) {
            AuditStatus::where('auditable_id', $entity->id)
                ->where('type', $body['type'])
                ->where('is_active', true)
                ->update(['is_active' => false]);
            $this->saveAuditStatus(get_class($entity), $entity->id, $status, $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        }

        return $entity->createResource();
    }

    private function canChangeStatus($entity, $status): bool
    {
        switch(get_class($entity)) {
            case 'App\Models\V2\Sites\Site':
                return $this->canChangeSiteStatusTo($entity, $status);
            case 'App\Models\V2\Sites\SitePolygon':
                return $this->canChangeSitePolygonStatusTo($entity, $status);
            default:
                return true;
        }
    }

    private function canChangeSiteStatusTo($entity, $status)
    {
        if ($status === 'approved') {
            return ! SitePolygon::where('site_id', $entity->id)->where('status', 'approved')->exists();
        }

        return true;
    }

    private function canChangeSitePolygonStatusTo($entity, $status)
    {
        //TODO ask Cesar how to handle this one.
        return true;
    }
}

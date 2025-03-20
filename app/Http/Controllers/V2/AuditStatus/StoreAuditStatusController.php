<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\AuditStatus\AuditStatusCreateRequest;
use App\Http\Resources\V2\AuditStatusResource;
use App\Jobs\V2\SendProjectManagerJob as SendProjectManagerJobs;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\PolygonUpdates;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;

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
            if ($auditable instanceof SitePolygon) {
                $user = auth()->user();
                PolygonUpdates::create([
                    'site_polygon_uuid' => $auditable->primary_uuid,
                    'version_name' => $auditable->version_name,
                    'change' => 'New Comment',
                    'updated_by_id' => $user->id,
                    'comment' => $body['comment'],
                    'type' => 'update',
                ]);
            }
            if ($body['type'] == 'comment' && get_class($auditable) != SitePolygon::class) {
                SendProjectManagerJobs::dispatch($auditable);
            }
            if (get_class($auditable) === SitePolygon::class) {
                $sitePolygon = Site::where('uuid', $auditable->site_id)->first();
                SendProjectManagerJobs::dispatch($sitePolygon);
            }
        }
        $auditStatus->entity_name = $auditable->name;

        return new AuditStatusResource($auditStatus);
    }
}

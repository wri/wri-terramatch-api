<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\AuditStatus\AuditStatusRequest;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;

class StoreAuditStatusController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(AuditStatusRequest $auditStatusRequest): AuditStatusResource
    {
        $body = $auditStatusRequest->all();

        $model = $this->getEntityFromRequest($auditStatusRequest);
        if ($body['type'] === 'change-request') {
            AuditStatus::where([
                ['auditable_id', $model->id],
                ['type', 'change-request'],
                ['is_active', true],
            ])->update(['is_active' => false]);
            $auditStatusResponse = $this->saveAuditStatus(get_class($model), $model->id, $body['status'], $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        } else {
            $auditStatusResponse = $this->saveAuditStatus(get_class($model), $model->id, $body['status'], $body['comment'], $body['type']);
        }
        $auditStatusResponse->entity_name = $model->name;

        return new AuditStatusResource($auditStatusResponse);
    }

    private function getEntityFromRequest(Request $request)
    {
        switch ($request->get('auditable_type')) {
            case 'Site':
                return Site::isUuid($request->get('auditable_uuid'))->firstOrFail();
            case 'Project':
                return Project::isUuid($request->get('auditable_uuid'))->firstOrFail();
            case 'SitePolygon':
                return SitePolygon::isUuid($request->get('auditable_uuid'))->firstOrFail();

            default:
                throw new InvalidMorphableModelException();
        }
    }
}

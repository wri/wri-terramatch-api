<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Http\Controllers\Controller;
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

    public function __invoke(Request $request): AuditStatusResource
    {
        $body = $request->all();

        $auditable_type = $this->getAuditableTypeFromRequest($request);
        if ($body['type'] === 'change-request') {
            AuditStatus::where([
                ['auditable_id', $body['auditable_id']],
                ['type', 'change-request'],
                ['is_active', true],
            ])->update(['is_active' => false]);
            $auditStatusresponse = $this->saveAuditStatus($auditable_type, $body['auditable_id'], $body['status'], $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        } else {
            $auditStatusresponse = $this->saveAuditStatus($auditable_type, $body['auditable_id'], $body['status'], $body['comment'], $body['type']);
        }
        return new AuditStatusResource($auditStatusresponse);
    }

    private function getAuditableTypeFromRequest(Request $request)
    {
        switch ($request->get('auditable_type')) {
            case 'Site':
                return Site::class;
            case 'Project':
                return Project::class;
            case 'SitePolygon':
                return SitePolygon::class;

            default:
                throw new InvalidMorphableModelException();
        }
    }
}

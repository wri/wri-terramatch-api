<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use App\Models\V2\Sites\Site;

class GetAuditStatusController extends Controller
{
    public function __invoke(Request $request, string $id = null)
    {
        $auditStatus = AuditStatus::where('entity', $request->input('entity'))
            ->where('entity_uuid', $request->input('uuid'))
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $audit_statuses_with_entity = $auditStatus->map(function ($audit) {
            $audit_with_entity = [];
            $audit_with_entity['id'] = $audit->id;
            $audit_with_entity['entity'] = $this->getEntity($audit->entity, $audit->entity_uuid)->name;
            $audit_with_entity['status'] = $audit->status;
            $audit_with_entity['comment'] = $audit->comment;
            $audit_with_entity['attachment_url'] = $audit->attachment_url;
            $audit_with_entity['date_created'] = $audit->date_created;
            $audit_with_entity['created_by'] = $audit->created_by;
            return $audit_with_entity;
        });
        return AuditStatusResource::collection($audit_statuses_with_entity);
    }

    private function getEntity($entity, $entity_uuid)
    {
        switch ($entity) {
            case 'Site':
                return Site::where('uuid', $entity_uuid)->first();
                break;

            case 'Project':
                return Project::where('uuid', $entity_uuid)->first();
                break;

            default:
                # code...
                break;
        }
        return $entity::where('uuid', $entity_uuid)->first();
    }
}

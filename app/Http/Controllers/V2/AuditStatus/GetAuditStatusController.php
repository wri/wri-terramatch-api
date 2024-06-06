<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\Request;

class GetAuditStatusController extends Controller
{
    public function __invoke(Request $request, string $id = null)
    {
        $auditStatus = AuditStatus::where('entity', $request->input('entity'))
            ->where('entity_uuid', $request->input('uuid'))
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->with('auditAttachments')
            ->get();

        $audit_statuses_with_entity = $this->mapAuditStatus($auditStatus);

        return AuditStatusResource::collection($audit_statuses_with_entity);
    }

    private function mapAuditStatus($auditStatus)
    {
        return $auditStatus->map(function ($audit) {
            return [
                'id' => $audit->id,
                'entity' => $this->getEntity($audit->entity, $audit->entity_uuid)->name,
                'entity_uuid' => $audit->entity_uuid,
                'status' => $audit->status,
                'comment' => $audit->comment,
                'date_created' => $audit->date_created,
                'created_by' => $audit->created_by,
                'type' => $audit->type,
                'is_submitted' => $audit->is_submitted,
                'is_active' => $audit->is_active,
                'first_name' => $audit->first_name,
                'last_name' => $audit->last_name,
                'request_removed' => $audit->request_removed,
                'attachments' => $this->mapAttachments($audit->auditAttachments),
            ];
        });
    }

    private function mapAttachments($attachments)
    {
        return $attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'entity_id' => $attachment->entity_id,
                'file_name' => $attachment->file_name,
                'file_url' => $attachment->file_url,
                'date_created' => $attachment->date_created,
                'created_by' => $attachment->created_by,
            ];
        });
    }

    private function getEntity($entity, $entity_uuid)
    {
        switch ($entity) {
            case 'Site':
                return Site::where('uuid', $entity_uuid)->first();

            case 'Project':
                return Project::where('uuid', $entity_uuid)->first();

            case 'SitePolygon':
                return SitePolygon::where('uuid', $entity_uuid)->first();

            default:
                # code...
                break;
        }

        return $entity::where('uuid', $entity_uuid)->first();
    }
}

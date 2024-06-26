<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\V2\AuditableModel;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
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

        $transformedAudits = $this->transformAudits($this->getAudits($auditable), $auditable);

        $combinedData = $auditStatuses->concat($transformedAudits);

        return AuditStatusResource::collection($combinedData);
    }

    private function getAudits($auditable)
    {
        $auditsModelInstance = $this->getModelInstance($auditable);
        if (! $auditsModelInstance) {
            return collect();
        }

        $audits = $auditsModelInstance->audits()
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $audits;
    }

    private function transformAudits($audits, $auditable)
    {
        return $audits->map(function ($audit) use ($auditable) {
            $data = [
                'id' => $audit->id,
                'uuid' => $audit->uuid,
                'entity_name' => $auditable->getAuditableNameAttribute(),
                'status' => $audit->new_values['status'] ?? null,
                'comment' => $audit->new_values['feedback'] ?? null,
                'first_name' => $audit->user->first_name ?? null,
                'last_name' => $audit->user->last_name ?? null,
                'type' => 'status',
                'is_submitted' => $audit->is_submitted ?? null,
                'is_active' => $audit->is_active ?? null,
                'request_removed' => $audit->request_removed ?? null,
                'date_created' => $audit->created_at ?? null,
                'created_by' => $audit->user_id ?? null,
            ];

            return (object) $data;
        });
    }

    private function getModelInstance(AuditableModel $auditable)
    {
        $modelClass = $this->getModel(get_class($auditable));
        if (! $modelClass) {
            return null;
        }

        return $modelClass::isUuid($auditable->uuid)->first();
    }

    private function getModel(string $entity)
    {
        $model = null;

        switch ($entity) {
            case 'App\Models\V2\Projects\Project':
                $model = Project::class;

                break;
            case 'App\Models\V2\Sites\Site':
                $model = Site::class;

                break;
            case 'App\Models\V2\Nurseries\Nursery':
                $model = Nursery::class;

                break;
            case 'App\Models\V2\Projects\ProjectReport':
                $model = ProjectReport::class;

                break;
            case 'App\Models\V2\Sites\SiteReport':
                $model = SiteReport::class;

                break;
            case 'App\Models\V2\Nurseries\NurseryReport':
                $model = NurseryReport::class;

                break;
        }

        return $model;
    }
}

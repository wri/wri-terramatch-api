<?php

namespace App\Http\Controllers\V2\UpdateRequests;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestResource;
use App\Models\Site;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\AuditCustom;

class AdminStatusUpdateRequestController extends Controller
{
    public function __invoke(StatusChangeRequest $request, UpdateRequest $updateRequest, string $status)
    {
        $data = $request->validated();
        $this->authorize($status, $updateRequest);

        switch($status) {
            case 'approve':
                $this->applyUpdates($updateRequest);
                $updateRequest->update([
                    'status' => UpdateRequest::STATUS_APPROVED,
                    'feedback' => data_get($data, 'feedback'),
                ]);

                $entity = $updateRequest->updaterequestable;
                $entity->update_request_status = UpdateRequest::STATUS_APPROVED;
                $entity->save();

                break;
            case 'reject':
                $updateRequest->update([
                    'status' => UpdateRequest::STATUS_REJECTED,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                $entity = $updateRequest->updaterequestable;
                $entity->update_request_status = UpdateRequest::STATUS_REJECTED;
                $entity->save();

                break;
            case 'moreinfo':
                $updateRequest->update([
                    'status' => UpdateRequest::STATUS_NEEDS_MORE_INFORMATION,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                $entity = $updateRequest->updaterequestable;
                $entity->update_request_status = UpdateRequest::STATUS_NEEDS_MORE_INFORMATION;
                $entity->save();

                break;
            default:
                return new JsonResponse('status not supported', 401);
        }

        $this->handleAction($request, $updateRequest);
        $this->updateAuditLog($updateRequest);

        return new UpdateRequestResource($updateRequest);
    }

    /** Update audit log */
    private function updateAuditLog(UpdateRequest $updateRequest)
    {
        $entity = $updateRequest->updaterequestable;

        if (! empty($entity)) {
            $entity->auditEvent = 'update';
            $entity->isCustomEvent = true;
            $entity->auditCustomOld = [
                'status' => $entity->status,
                'feedback' => $entity->feedback,
            ];
            $entity->auditCustomNew = [
                'status' => 'update-request-' . $updateRequest->status,
                'feedback' => $updateRequest->feedback,
            ];

            Event::dispatch(AuditCustom::class, $entity);
        }
    }

    private function applyUpdates(UpdateRequest $updateRequest)
    {
        $entity = $updateRequest->updaterequestable;
        $entityProps = $entity->mapEntityAnswers($updateRequest->content, $entity->getCurrentForm(), data_get($entity->getFormConfig(), 'fields', []));
        $entity->update($entityProps);
    }

    private function handleAction(StatusChangeRequest $request, UpdateRequest $updateRequest)
    {
        $entity = $updateRequest->updaterequestable;

        switch(get_class($entity)) {
            case Project::class :
                $title = $entity->name;
                $sub_title = '';

                break;
            case Site::class :
                $title = $entity->project->name;
                $sub_title = 'Site: ' . $entity->name;

                break;
            case Nursery::class :
                $title = $entity->project->name;
                $sub_title = 'Nursery: ' . $entity->name;

                break;
            case ProjectReport::class :
                $title = $entity->project->name;
                $sub_title = 'Project report';

                break;
            case SiteReport::class :
                $title = $entity->project->name;
                $sub_title = 'Site report: ' . $entity->site->name;

                break;
            case NurseryReport::class :
                $title = $entity->project->name;
                $sub_title = 'Nursery report: ' . $entity->nursery->name;

                break;
            default:
                $title = 'Update Request';
                $sub_title = '';
        }


        EntityStatusChangeEvent::dispatch($request->user(), $entity, $title, $sub_title, '');
    }
}

<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestResource;
use App\Models\V2\EntityModel;
use App\Models\V2\ReportModel;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\StateMachines\UpdateRequestStatusStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateEntityWithFormController extends Controller
{
    public function __invoke(EntityModel $entity, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $entity);
        $answers = data_get($formSubmissionRequest->validated(), 'answers', []);

        $form = $entity->getForm();
        if (empty($form)) {
            return new JsonResponse('No form schema found for this framework.', 404);
        }

        // TODO (NJC) This path will get an update in epic TM-558. The problem here is that admins are automatically
        //   putting reports into the approved state when updating them.
        if (Auth::user()->can('framework-' . $entity->framework_key)) {
            $config = data_get($entity->getFormConfig(), 'fields', []);
            $entity->update($entity->mapEntityAnswers($answers, $form, $config));
            $entity->approve();
            return $entity->createResource();
        }

        if ($entity->isEditable()) {
            $config = data_get($entity->getFormConfig(), 'fields', []);
            $entity->update($entity->mapEntityAnswers($answers, $form, $config));
            if ($entity instanceof ReportModel) {
                $entity->updateInProgress();
            }
            return $entity->createResource();
        }

        /** @var UpdateRequest $updateRequest */
        $updateRequest = $entity->updateRequests()->isUnapproved()->first();
        if (!empty($updateRequest)) {
            $updateRequest->content = array_merge($updateRequest->content, $answers);
            $updateRequest->save();
        } else {
            $updateRequest = UpdateRequest::create([
                'organisation_id' => $entity->organisation ? $entity->organisation->id : $entity->project->organisation_id,
                'project_id' => $entity->project ? $entity->project->id : $entity->id,
                'created_by_id' => Auth::user()->id,
                'framework_key' => $entity->framework_key,
                'updaterequestable_type' => get_class($entity),
                'updaterequestable_id' => $entity->id,
                'status' => UpdateRequestStatusStateMachine::DRAFT,
                'content' => $answers,
            ]);
            $entity->update(['update_request_status' => UpdateRequestStatusStateMachine::DRAFT]);
        }

        return new UpdateRequestResource($updateRequest);
    }
}

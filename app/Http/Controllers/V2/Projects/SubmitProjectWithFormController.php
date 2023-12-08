<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectWithSchemaResource;
use App\Models\V2\Action;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitProjectWithFormController extends Controller
{
    public function __invoke(Project $project, Request $request)
    {
        $this->authorize('submit', $project);

        $form = Form::where('model', Project::class)
            ->where('framework_key', $project->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No nursery form schema found for this framework.', 404);
        }

        $updateRequest = $project->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION])
            ->first();

        if (! empty($updateRequest)) {
            $updateRequest->status = UpdateRequest::STATUS_AWAITING_APPROVAL;
            $project->save();

            Action::where('targetable_type', UpdateRequest::class)
                ->where('targetable_id', $updateRequest->id)
                ->delete();

            Action::where('targetable_type', $updateRequest->updaterequestable_type)
                ->where('targetable_id', $updateRequest->updaterequestable_id)
                ->delete();
        } else {
            $project->status = Project::STATUS_AWAITING_APPROVAL;
            $project->save();

            Action::where('targetable_type', Project::class)
                ->where('targetable_id', $project->id)
                ->delete();
        }


        return new ProjectWithSchemaResource($project, ['schema' => $form]);
    }
}

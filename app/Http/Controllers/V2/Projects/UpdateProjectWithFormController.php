<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesUpdateRequests;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\Projects\ProjectResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateProjectWithFormController extends Controller
{
    use HandlesUpdateRequests;

    public function __invoke(Project $project, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $project);
        $data = $formSubmissionRequest->validated();
        $answers = data_get($data, 'answers', []);

        $form = Form::where('model', Project::class)
            ->where('framework_key', $project->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No project form schema found for this framework.', 404);
        }

        if (Auth::user()->can('framework-' . $project->framework_key)) {
            $entityProps = $project->mapEntityAnswers(data_get($data, 'answers', []), $form, config('wri.linked-fields.models.project.fields', []));
            $project->update($entityProps);

            $project->status = Project::STATUS_APPROVED;
            $project->save();

            return new ProjectResource($project);
        }

        if (! in_array($project->status, [Project::STATUS_AWAITING_APPROVAL, Project::STATUS_NEEDS_MORE_INFORMATION, Project::STATUS_APPROVED])) {
            $entityProps = $project->mapEntityAnswers(data_get($data, 'answers', []), $form, config('wri.linked-fields.models.project.fields', []));
            $project->update($entityProps);

            return new ProjectResource($project);
        }

        return $this->handleUpdateRequest($project, $answers);
    }
}

<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesUpdateRequests;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\ProjectReports\ProjectReportResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateProjectReportWithFormController extends Controller
{
    use HandlesUpdateRequests;

    public function __invoke(ProjectReport $projectReport, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $projectReport);
        $data = $formSubmissionRequest->validated();
        $answers = data_get($data, 'answers', []);

        $form = Form::where('model', ProjectReport::class)
            ->where('framework_key', $projectReport->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No project report form schema found for this framework.', 404);
        }

        if (Auth::user()->can('framework-' . $projectReport->framework_key)) {
            $entityProps = $projectReport->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.project-report.fields', []));
            $projectReport->update($entityProps);

            $projectReport->completion = $projectReport->calculateCompletion($form);
            $projectReport->completion_status = $this->getCompletionStatus($projectReport->completion);
            $projectReport->status = ProjectReport::STATUS_APPROVED;
            $projectReport->save();

            return new ProjectReportResource($projectReport);
        }

        if (! in_array($projectReport->status, [ProjectReport::STATUS_AWAITING_APPROVAL, ProjectReport::STATUS_NEEDS_MORE_INFORMATION, ProjectReport::STATUS_APPROVED])) {
            $entityProps = $projectReport->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.project-report.fields', []));
            $projectReport->update($entityProps);

            $projectReport->completion = $projectReport->calculateCompletion($form);
            $projectReport->completion_status = $this->getCompletionStatus($projectReport->completion);
            if (empty($projectReport->created_by)) {
                $projectReport->created_by = Auth::user()->id;
            }
            $projectReport->save();

            return new ProjectReportResource($projectReport);
        }

        return $this->handleUpdateRequest($projectReport, $answers);
    }

    private function getCompletionStatus(int $completion): string
    {
        if ($completion == 0) {
            return ProjectReport::COMPLETION_STATUS_NOT_STARTED;
        } elseif ($completion == 100) {
            return ProjectReport::COMPLETION_STATUS_COMPLETE;
        }

        return ProjectReport::COMPLETION_STATUS_STARTED;
    }
}

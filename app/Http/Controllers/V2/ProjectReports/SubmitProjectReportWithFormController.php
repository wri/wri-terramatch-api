<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectReports\ProjectReportWithSchemaResource;
use App\Models\V2\Action;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitProjectReportWithFormController extends Controller
{
    public function __invoke(ProjectReport $projectReport, Request $request)
    {
        $this->authorize('submit', $projectReport);

        $form = Form::where('model', ProjectReport::class)
            ->where('framework_key', $projectReport->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No nursery form schema found for this framework.', 404);
        }

        $updateRequest = $projectReport->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION])
            ->first();

        if (! empty($updateRequest)) {
            $updateRequest->status = UpdateRequest::STATUS_AWAITING_APPROVAL;
            $projectReport->save();

            Action::where('targetable_type', UpdateRequest::class)
                ->where('targetable_id', $updateRequest->id)
                ->delete();

            Action::where('targetable_type', $updateRequest->updaterequestable_type)
                ->where('targetable_id', $updateRequest->updaterequestable_id)
                ->delete();
        } else {
            $projectReport->status = ProjectReport::STATUS_AWAITING_APPROVAL;
            $projectReport->completion = 100;
            $projectReport->completion_status = ProjectReport::COMPLETION_STATUS_COMPLETE;
            $projectReport->submitted_at = now();
            $projectReport->save();

            Action::where('targetable_type', ProjectReport::class)
                ->where('targetable_id', $projectReport->id)
                ->delete();
        }

        return new ProjectReportWithSchemaResource($projectReport, ['schema' => $form]);
    }
}

<?php

namespace App\Http\Controllers\V2\Reports;

use App\Http\Controllers\Controller;
use App\Models\V2\Action;
use App\Models\V2\ReportModel;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubmitReportWithFormController extends Controller
{
    public function __invoke(ReportModel $report, Request $request)
    {
        $this->authorize('submit', $report);

        $form = $report->getForm();
        if (empty($form)) {
            return new JsonResponse('No report form schema found for this framework.', 404);
        }

        $updateRequest = $report->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION
            ])
        ->first();

        if (!empty($updateRequest)) {
            $updateRequest->update(['status' => UpdateRequest::STATUS_AWAITING_APPROVAL]);
            $report->update(['update_request_status' => UpdateRequest::STATUS_AWAITING_APPROVAL]);
            $report->task->checkStatus();

            Action::where('targetable_type', get_class($report))
                ->where('targetable_id', $updateRequest->id)
                ->delete();
            Action::where('targetable_type', $updateRequest->updaterequestable_type)
                ->where('targetable_id', $updateRequest->updaterequestable_id)
                ->delete();
        } else {
            $report->awaitingApproval();
            Action::where('targetable_type', get_class($report))
                ->where('targetable_id', $report->id)
                ->delete();
        }

        return $report->createSchemaResource();
    }
}

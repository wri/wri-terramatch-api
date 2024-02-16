<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Nurseries\NurseyWithSchemaResource;
use App\Models\V2\Action;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitNurseryReportWithFormController extends Controller
{
    public function __invoke(NurseryReport $nurseryReport, Request $request)
    {
        $this->authorize('submit', $nurseryReport);

        $form = Form::where('model', NurseryReport::class)
            ->where('framework_key', $nurseryReport->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No nursery report form schema found for this framework.', 404);
        }

        $updateRequest = $nurseryReport->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION])
            ->first();

        if (! empty($updateRequest)) {
            $updateRequest->update([ 'status' => UpdateRequest::STATUS_AWAITING_APPROVAL ]);
            $nurseryReport->update([ 'update_request_status' => UpdateRequest::STATUS_AWAITING_APPROVAL ]);

            Action::where('targetable_type', UpdateRequest::class)
                ->where('targetable_id', $updateRequest->id)
                ->delete();

            Action::where('targetable_type', $updateRequest->updaterequestable_type)
                ->where('targetable_id', $updateRequest->updaterequestable_id)
                ->delete();
        } else {
            $nurseryReport->status = NurseryReport::STATUS_AWAITING_APPROVAL;
            $nurseryReport->completion = 100;
            $nurseryReport->completion_status = NurseryReport::COMPLETION_STATUS_COMPLETE;
            $nurseryReport->submitted_at = now();
            $nurseryReport->save();

            Action::where('targetable_type', NurseryReport::class)
                ->where('targetable_id', $nurseryReport->id)
                ->delete();
        }

        return new NurseyWithSchemaResource($nurseryReport, ['schema' => $form]);
    }
}

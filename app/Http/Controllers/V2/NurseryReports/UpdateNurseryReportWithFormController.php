<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesUpdateRequests;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\NurseryReports\NurseryReportResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateNurseryReportWithFormController extends Controller
{
    use HandlesUpdateRequests;

    public function __invoke(NurseryReport $nurseryReport, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $nurseryReport);
        $data = $formSubmissionRequest->validated();
        $answers = data_get($data, 'answers', []);

        $form = Form::where('model', NurseryReport::class)
            ->where('framework_key', $nurseryReport->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No nursery report form schema found for this framework.', 404);
        }

        if (Auth::user()->can('framework-' . $nurseryReport->framework_key)) {
            $entityProps = $nurseryReport->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.nursery-report.fields', []));
            $nurseryReport->update($entityProps);

            $nurseryReport->completion = $nurseryReport->calculateCompletion($form);
            $nurseryReport->completion_status = $this->getCompletionStatus($nurseryReport->completion);
            $nurseryReport->status = NurseryReport::STATUS_APPROVED;
            $nurseryReport->save();

            return new NurseryReportResource($nurseryReport);
        }

        if (! in_array($nurseryReport->status, [NurseryReport::STATUS_AWAITING_APPROVAL, NurseryReport::STATUS_NEEDS_MORE_INFORMATION, NurseryReport::STATUS_APPROVED])) {
            $entityProps = $nurseryReport->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.nursery-report.fields', []));
            $nurseryReport->update($entityProps);

            $nurseryReport->completion = $nurseryReport->calculateCompletion($form);
            $nurseryReport->completion_status = $this->getCompletionStatus($nurseryReport->completion);
            if (empty($nurseryReport->created_by)) {
                $nurseryReport->created_by = Auth::user()->id;
            }
            $nurseryReport->save();

            return new NurseryReportResource($nurseryReport);
        }

        return $this->handleUpdateRequest($nurseryReport, $answers);
    }

    private function getCompletionStatus(int $completion): string
    {
        if ($completion == 0) {
            return NurseryReport::COMPLETION_STATUS_NOT_STARTED;
        } elseif ($completion == 100) {
            return NurseryReport::COMPLETION_STATUS_COMPLETE;
        }

        return NurseryReport::COMPLETION_STATUS_STARTED;
    }
}

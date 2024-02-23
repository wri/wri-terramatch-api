<?php

namespace App\Http\Controllers\V2\Reports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesUpdateRequests;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Models\V2\ReportModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateReportWithFormController extends Controller
{
    use HandlesUpdateRequests;

    public function __invoke(ReportModel $report, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $report);
        $answers = data_get($formSubmissionRequest->validated(), 'answers', []);

        $form = $report->getForm();
        if (empty($form)) {
            return new JsonResponse('No report form schema found for this framework.', 404);
        }

        // TODO (NJC) This path will get an update in epic TM-558. The problem here is that admins are automatically
        //   putting reports into the approved state when updating them.
        if (Auth::user()->can('framework-' . $report->framework_key)) {
            $report->update($report->mapEntityAnswers($answers, $form, $report->getLinkedFieldsConfig()));
            $report->approve();
            return $report->createResource();
        }

        if ($report->isEditable()) {
            $report->update($report->mapEntityAnswers($answers, $form, $report->getLinkedFieldsConfig()));
            $report->updateInProgress();
            return $report->createResource();
        }

        return $this->handleUpdateRequest($report, $answers);
    }
}

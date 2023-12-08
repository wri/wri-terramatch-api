<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesUpdateRequests;
use App\Http\Requests\V2\Forms\UpdateFormSubmissionRequest;
use App\Http\Resources\V2\SiteReports\SiteReportResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UpdateSiteReportWithFormController extends Controller
{
    use HandlesUpdateRequests;

    public function __invoke(SiteReport $siteReport, UpdateFormSubmissionRequest $formSubmissionRequest)
    {
        $this->authorize('update', $siteReport);
        $data = $formSubmissionRequest->validated();
        $answers = data_get($data, 'answers', []);

        $form = Form::where('model', SiteReport::class)
            ->where('framework_key', $siteReport->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No site report form schema found for this framework.', 404);
        }


        if (Auth::user()->can('framework-' . $siteReport->framework_key)) {
            $entityProps = $siteReport->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.site-report.fields', []));
            $siteReport->update($entityProps);

            $siteReport->completion = $siteReport->calculateCompletion($form);
            $siteReport->completion_status = $this->getCompletionStatus($siteReport->completion);
            $siteReport->status = SiteReport::STATUS_APPROVED;
            $siteReport->save();

            return new SiteReportResource($siteReport);
        }

        if (! in_array($siteReport->status, [SiteReport::STATUS_AWAITING_APPROVAL, SiteReport::STATUS_NEEDS_MORE_INFORMATION, SiteReport::STATUS_APPROVED])) {
            $entityProps = $siteReport->mapEntityAnswers($answers, $form, config('wri.linked-fields.models.site-report.fields', []));
            $siteReport->update($entityProps);

            $siteReport->completion = $siteReport->calculateCompletion($form);
            $siteReport->completion_status = $this->getCompletionStatus($siteReport->completion);
            if (empty($siteReport->created_by)) {
                $siteReport->created_by = Auth::user()->id;
            }
            $siteReport->save();

            return new SiteReportResource($siteReport);
        }

        return $this->handleUpdateRequest($siteReport, $answers);
    }

    private function getCompletionStatus(int $completion): string
    {
        if ($completion == 0) {
            return SiteReport::COMPLETION_STATUS_NOT_STARTED;
        } elseif ($completion == 100) {
            return SiteReport::COMPLETION_STATUS_COMPLETE;
        }

        return SiteReport::COMPLETION_STATUS_STARTED;
    }
}

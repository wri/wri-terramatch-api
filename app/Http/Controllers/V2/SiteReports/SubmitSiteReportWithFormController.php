<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportWithSchemaResource;
use App\Models\V2\Action;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitSiteReportWithFormController extends Controller
{
    public function __invoke(SiteReport $siteReport, Request $request)
    {
        $this->authorize('submit', $siteReport);

        $form = Form::where('model', SiteReport::class)
            ->where('framework_key', $siteReport->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No site report form schema found for this framework.', 404);
        }

        $updateRequest = $siteReport->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION])
            ->first();

        if (! empty($updateRequest)) {
            $updateRequest->status = UpdateRequest::STATUS_AWAITING_APPROVAL;
            $siteReport->save();

            Action::where('targetable_type', UpdateRequest::class)
                ->where('targetable_id', $updateRequest->id)
                ->delete();

            Action::where('targetable_type', $updateRequest->updaterequestable_type)
                ->where('targetable_id', $updateRequest->updaterequestable_id)
                ->delete();
        } else {
            $siteReport->status = SiteReport::STATUS_AWAITING_APPROVAL;
            $siteReport->completion = 100;
            $siteReport->completion_status = SiteReport::COMPLETION_STATUS_COMPLETE;
            $siteReport->submitted_at = now();
            $siteReport->save();

            Action::where('targetable_type', SiteReport::class)
                ->where('targetable_id', $siteReport->id)
                ->delete();
        }

        return new SiteReportWithSchemaResource($siteReport, ['schema' => $form]);
    }
}

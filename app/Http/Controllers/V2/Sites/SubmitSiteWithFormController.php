<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportWithSchemaResource;
use App\Models\V2\Action;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitSiteWithFormController extends Controller
{
    public function __invoke(Site $site, Request $request)
    {
        $this->authorize('submit', $site);

        $form = Form::where('model', SiteReport::class)
            ->where('framework_key', $site->framework_key)
            ->first();

        if (empty($form)) {
            return new JsonResponse('No site form schema found for this framework.', 404);
        }

        $updateRequest = $site->updateRequests()
            ->whereIn('status', [
                UpdateRequest::STATUS_AWAITING_APPROVAL,
                UpdateRequest::STATUS_REQUESTED,
                UpdateRequest::STATUS_DRAFT,
                UpdateRequest::STATUS_NEEDS_MORE_INFORMATION])
            ->first();

        if (! empty($updateRequest)) {
            $updateRequest->status = UpdateRequest::STATUS_AWAITING_APPROVAL;
            $site->save();

            Action::where('targetable_type', UpdateRequest::class)
                ->where('targetable_id', $updateRequest->id)
                ->delete();

            Action::where('targetable_type', $updateRequest->updaterequestable_type)
                ->where('targetable_id', $updateRequest->updaterequestable_id)
                ->delete();
        } else {
            $site->status = Site::STATUS_AWAITING_APPROVAL;
            $site->save();

            Action::where('targetable_type', Site::class)
                ->where('targetable_id', $site->id)
                ->delete();
        }

        return new SiteReportWithSchemaResource($site, ['schema' => $form]);
    }
}

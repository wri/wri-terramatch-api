<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Http\Resources\V2\SiteReports\SiteReportResource;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\JsonResponse;

class AdminStatusSiteReportController extends Controller
{
    public function __invoke(StatusChangeRequest $request, SiteReport $siteReport, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $siteReport);

        switch($status) {
            case 'approve':
                $siteReport->update(['status' => SiteReport::STATUS_APPROVED,
                    'feedback' => data_get($data, 'feedback'),
                ]);

                break;
            case 'moreinfo':
                $siteReport->update([
                    'status' => SiteReport::STATUS_NEEDS_MORE_INFORMATION,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                break;
            default:
                return new JsonResponse('status not supported', 401);
        }

        EntityStatusChangeEvent::dispatch($request->user(), $siteReport);

        return new SiteReportResource($siteReport);
    }
}

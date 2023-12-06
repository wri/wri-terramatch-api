<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Http\Resources\V2\ProjectReports\ProjectReportResource;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\JsonResponse;

class AdminStatusProjectReportController extends Controller
{
    public function __invoke(StatusChangeRequest $request, ProjectReport $projectReport, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $projectReport);

        switch($status) {
            case 'approve':
                $projectReport->update(['status' => ProjectReport::STATUS_APPROVED,
                    'feedback' => data_get($data, 'feedback'),
                ]);

                break;
            case 'moreinfo':
                $projectReport->update([
                    'status' => ProjectReport::STATUS_NEEDS_MORE_INFORMATION,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                break;
            default:
                return new JsonResponse('status not supported', 401);
        }

        EntityStatusChangeEvent::dispatch($request->user(), $projectReport);

        return new ProjectReportResource($projectReport);
    }
}

<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Http\Resources\V2\NurseryReports\NurseryReportResource;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\JsonResponse;

class AdminStatusNurseryReportController extends Controller
{
    public function __invoke(StatusChangeRequest $request, NurseryReport $nurseryReport, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $nurseryReport);

        switch($status) {
            case 'approve':
                $nurseryReport->update(['status' => NurseryReport::STATUS_APPROVED,
                    'feedback' => data_get($data, 'feedback'),
                ]);

                break;
            case 'moreinfo':
                $nurseryReport->update([
                    'status' => NurseryReport::STATUS_NEEDS_MORE_INFORMATION,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                break;
            default:
                return new JsonResponse('status not supported', 401);
        }

        EntityStatusChangeEvent::dispatch($request->user(), $nurseryReport);

        return new NurseryReportResource($nurseryReport);
    }
}

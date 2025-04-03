<?php

namespace App\Http\Controllers\V2\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Models\V2\ReportModel;
use Illuminate\Http\JsonResponse;

class AdminStatusReportController extends Controller
{
    public function __invoke(StatusChangeRequest $request, ReportModel $report, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $report);

        switch ($status) {
            case 'approve':
                $report->approve(data_get($data, 'feedback'));

                break;

            case 'moreinfo':
                $report->needsMoreInformation(data_get($data, 'feedback'), data_get($data, 'feedback_fields'));

                break;

            default:
                return new JsonResponse('status not supported', 401);
        }

        $report->dispatchStatusChangeEvent($request->user());

        return $report->createResource();
    }
}

<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\NurseryReports\NurseryReportResource;
use App\Models\V2\Nurseries\NurseryReport;

class NothingToReportNurseryReportController extends Controller
{
    public function __invoke(NurseryReport $nurseryReport): NurseryReportResource
    {
        $this->authorize('read', $nurseryReport);

        $nurseryReport->update([
            'status' => NurseryReport::STATUS_AWAITING_APPROVAL,
            'nothing_to_report' => true,
            'completion_status' => NurseryReport::COMPLETION_STATUS_COMPLETE,
            'completion' => 100,
        ]);

        return new NurseryReportResource($nurseryReport);
    }
}

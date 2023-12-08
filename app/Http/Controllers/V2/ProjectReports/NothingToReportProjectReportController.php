<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectReports\ProjectReportResource;
use App\Models\V2\Projects\ProjectReport;

class NothingToReportProjectReportController extends Controller
{
    public function __invoke(ProjectReport $projectReport): ProjectReportResource
    {
        $this->authorize('read', $projectReport);

        $projectReport->update([
            'status' => ProjectReport::STATUS_AWAITING_APPROVAL,
            'nothing_to_report' => true,
            'submitted_at' => now(),
        ]);

        return new ProjectReportResource($projectReport);
    }
}

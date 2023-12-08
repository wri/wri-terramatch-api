<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportResource;
use App\Models\V2\Sites\SiteReport;

class NothingToReportSiteReportController extends Controller
{
    public function __invoke(SiteReport $siteReport): SiteReportResource
    {
        $this->authorize('read', $siteReport);

        $siteReport->update([
            'status' => SiteReport::STATUS_AWAITING_APPROVAL,
            'nothing_to_report' => true,
            'completion_status' => SiteReport::COMPLETION_STATUS_COMPLETE,
            'completion' => 100,
        ]);

        return new SiteReportResource($siteReport);
    }
}

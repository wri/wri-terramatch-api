<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportResource;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;

class AdminSoftDeleteSiteReportController extends Controller
{
    public function __invoke(Request $request, SiteReport $siteReport): SiteReportResource
    {
        $this->authorize('delete', $siteReport);

        $siteReport->delete();

        return new SiteReportResource($siteReport);
    }
}

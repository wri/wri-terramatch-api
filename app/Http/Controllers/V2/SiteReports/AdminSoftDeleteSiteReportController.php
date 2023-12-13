<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Events\V2\General\EntityDeleteEvent;
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

        EntityDeleteEvent::dispatch($request->user(), $siteReport);

        return new SiteReportResource($siteReport);
    }
}

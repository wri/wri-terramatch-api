<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Events\V2\General\EntityDeleteEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportResource;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;

class AdminSoftDeleteProjectReportController extends Controller
{
    public function __invoke(Request $request, ProjectReport $projectReport): SiteReportResource
    {
        $this->authorize('delete', $projectReport);

        $projectReport->delete();

        EntityDeleteEvent::dispatch($request->user(), $projectReport);

        return new SiteReportResource($projectReport);
    }
}

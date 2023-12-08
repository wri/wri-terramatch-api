<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectReports\ProjectReportResource;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;

class ViewProjectReportController extends Controller
{
    public function __invoke(Request $request, ProjectReport $projectReport): ProjectReportResource
    {
        $this->authorize('read', $projectReport);

        return new ProjectReportResource($projectReport);
    }
}

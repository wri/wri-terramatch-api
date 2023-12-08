<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\NurseryReports\NurseryReportResource;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;

class ViewNurseryReportController extends Controller
{
    public function __invoke(Request $request, NurseryReport $nurseryReport): NurseryReportResource
    {
        $this->authorize('read', $nurseryReport);

        return new NurseryReportResource($nurseryReport);
    }
}

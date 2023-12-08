<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\NurseryReports\NurseryReportResource;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;

class AdminSoftDeleteNurseryReportController extends Controller
{
    public function __invoke(Request $request, NurseryReport $nurseryReport): NurseryReportResource
    {
        $this->authorize('delete', $nurseryReport);

        $nurseryReport->delete();

        return new NurseryReportResource($nurseryReport);
    }
}

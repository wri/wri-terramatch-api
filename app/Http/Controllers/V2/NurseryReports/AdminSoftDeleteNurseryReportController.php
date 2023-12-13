<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Events\V2\General\EntityDeleteEvent;
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

        EntityDeleteEvent::dispatch($request->user(), $nurseryReport);

        return new NurseryReportResource($nurseryReport);
    }
}

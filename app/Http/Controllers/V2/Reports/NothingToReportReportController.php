<?php

namespace App\Http\Controllers\V2\Reports;

use App\Http\Controllers\Controller;
use App\Models\V2\ReportModel;
use Illuminate\Http\Resources\Json\JsonResource;

class NothingToReportReportController extends Controller
{
    public function __invoke(ReportModel $report): JsonResource
    {
        $this->authorize('update', $report);
        $report->nothingToReport();
        return $report->createResource();
    }
}

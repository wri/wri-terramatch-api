<?php

namespace App\Http\Controllers\V2\Reports;

use App\Http\Controllers\Controller;
use App\Models\V2\ReportModel;

class ViewReportWithFormController extends Controller
{
    public function __invoke(ReportModel $report)
    {
        $this->authorize('read', $report);
        return $report->createSchemaResource();
    }
}

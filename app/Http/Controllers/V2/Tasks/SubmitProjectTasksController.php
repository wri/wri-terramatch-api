<?php

namespace App\Http\Controllers\V2\Tasks;

use App\Http\Controllers\Controller;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\ReportModel;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitProjectTasksController extends Controller
{
    public function __invoke(Request $request, Task $task): JsonResponse
    {
        $project = $task->project;
        $this->authorize('read', $project);

        $task->projectReport->awaitingApproval();
        $this->updateReports($task->siteReports);
        $this->updateReports($task->nurseryReports);

        $task->update(['status' => Task::STATUS_COMPLETE]);

        return new JsonResponse('Reports successfully submitted', 200);
    }

    private function updateReports(array $reports) {
        foreach ($reports as $report) {
            if ($report->completion == 0) {
                $report->nothingToReport();
            } else {
                $report->awaitingApproval();
            }
        }
    }
}

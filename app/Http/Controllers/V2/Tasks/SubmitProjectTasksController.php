<?php

namespace App\Http\Controllers\V2\Tasks;

use App\Http\Controllers\Controller;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitProjectTasksController extends Controller
{
    private $month;

    private $year;

    public function __invoke(Request $request, Task $task): JsonResponse
    {
        $project = $task->project;
        $this->authorize('read', $project);

        $this->month = $task->due_at->month;
        $this->year = $task->due_at->year;


        $this->updateProjectReports($project);
        $this->updateSiteReports($project);
        $this->updateNurseryReports($project);

        $task->update(['status' => Task::STATUS_COMPLETE]);

        return new JsonResponse('Reports successfully submitted', 200);
    }

    private function updateProjectReports(Project $project)
    {
        $projectReports = ProjectReport::where('project_id', $project->id)
            ->whereMonth('due_at', $this->month)
            ->whereYear('due_at', $this->year)
            ->get();

        foreach ($projectReports as $projectReport) {
            $data = [
                'completion' => 100,
                'completion_status' => ProjectReport::COMPLETION_STATUS_COMPLETE,
            ];

            $data['status'] = ProjectReport::STATUS_AWAITING_APPROVAL;
            $data['submitted_at'] = now();

            $projectReport->update($data);
        }
    }

    private function updateSiteReports(Project $project)
    {
        $siteIds = $project->sites()->pluck('id')->toArray();
        $siteReports = SiteReport::whereIn('site_id', $siteIds)
            ->whereMonth('due_at', $this->month)
            ->whereYear('due_at', $this->year)
            ->get();

        foreach ($siteReports as $siteReport) {
            $data = [
                'completion' => 100,
                'completion_status' => ProjectReport::COMPLETION_STATUS_COMPLETE,
            ];

            $data['status'] = SiteReport::STATUS_AWAITING_APPROVAL;
            $data['nothing_to_report'] = $siteReport->completion == 0;
            $data['submitted_at'] = now();
            $siteReport->update($data);
        }
    }

    private function updateNurseryReports(Project $project)
    {
        $nurseryIds = $project->nurseries()->pluck('id')->toArray();
        $nurseryReports = NurseryReport::whereIn('nursery_id', $nurseryIds)
            ->whereMonth('due_at', $this->month)
            ->whereYear('due_at', $this->year)
            ->get();

        foreach ($nurseryReports as $nurseryReport) {
            $data = [
                'completion' => 100,
                'completion_status' => NurseryReport::COMPLETION_STATUS_COMPLETE,
            ];

            $data['status'] = NurseryReport::STATUS_AWAITING_APPROVAL;
            $data['nothing_to_report'] = $nurseryReport->completion == 0;
            $data['submitted_at'] = now();
            $nurseryReport->update($data);
        }
    }
}

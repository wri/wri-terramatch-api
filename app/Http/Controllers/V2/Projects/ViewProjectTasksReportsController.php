<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Tasks\TaskReportsCollection;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewProjectTasksReportsController extends Controller
{
    public function __invoke(Request $request, Task $task)
    {
        $project = $task->project;
        $this->authorize('read', $project);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $month = $task->due_at->month;
        $year = $task->due_at->year;

        $date = clone $task->due_at;
        $date->subMonths(1);
        $wEnd = $date->format('F Y');

        $date->subMonths(2);
        $ppcStart = $date->format('F');
        $date->subMonths(3);
        $terrafundStart = $date->format('F');

        $ppcSuffix = $ppcStart . ' ' . $wEnd;
        $terrafundSuffix = $terrafundStart . ' ' . $wEnd;

        $siteIds = $project->sites()->pluck('id')->toArray();
        $nurseryIds = $project->nurseries()->pluck('id')->toArray();
        $columns = ['uuid', 'status', 'due_at', 'submitted_at', 'title', 'completion', 'update_request_status', 'updated_at'];

        $projectReports = DB::table('v2_project_reports')
            ->select($columns)
            ->selectRaw('"project-report" as type')
            ->selectRaw('(SELECT name FROM v2_projects WHERE v2_projects.id = v2_project_reports.project_id) as parent_name')
            ->selectRaw('false as nothing_to_report')
            ->selectRaw('IF(framework_key = "ppc", title, "Project Report ' . $terrafundSuffix . '") AS report_title')
            ->where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year);

        $siteReports = DB::table('v2_site_reports')
            ->select($columns)
            ->selectRaw('"site-report" as type ')
            ->selectRaw('(SELECT name FROM v2_sites WHERE v2_sites.id = v2_site_reports.site_id) as parent_name')
            ->selectRaw('nothing_to_report')
            ->selectRaw('IF(framework_key = "ppc", "Site Report ' . $ppcSuffix . '", "Site Report ' . $terrafundSuffix . '") AS report_title')
            ->whereIn('site_id', $siteIds)
            ->whereNull('deleted_at')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year);

        $collection = DB::table('v2_nursery_reports')
            ->select($columns)
            ->selectRaw('"nursery-report" as type ')
            ->selectRaw('(SELECT name FROM v2_nurseries WHERE v2_nurseries.id = v2_nursery_reports.nursery_id) as parent_name')
            ->selectRaw('nothing_to_report')
            ->selectRaw('"Nursery Report ' . $terrafundSuffix . '" AS report_title')
            ->whereIn('nursery_id', $nurseryIds)
            ->whereNull('deleted_at')
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->union($siteReports)
            ->union($projectReports)
        ->get();

        return new TaskReportsCollection($collection);
    }
}

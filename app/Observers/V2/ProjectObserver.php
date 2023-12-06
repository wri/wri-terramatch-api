<?php

namespace App\Observers\V2;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;

class ProjectObserver
{
    public function deleted(Project $project): void
    {
        $this->deleleNestedProjectReports($project);
        $this->deleleNestedProjectMonitorings($project);
        $this->deleleNestedNurseries($project);
        $this->deleleNestedSites($project);
    }

    private function deleleNestedProjectReports(Project $project): void
    {
        $reports = ProjectReport::where('project_id', $project->id)->get();
        foreach ($reports as $report) {
            $report->delete();
        }
    }

    private function deleleNestedNurseries(Project $project): void
    {
        $nurseries = Nursery::where('project_id', $project->id)->get();
        foreach ($nurseries as $nursery) {
            $nursery->delete();
        }
    }

    private function deleleNestedSites(Project $project): void
    {
        $sites = Site::where('project_id', $project->id)->get();
        foreach ($sites as $site) {
            $site->delete();
        }
    }

    private function deleleNestedProjectMonitorings(Project $project): void
    {
        $monitorings = ProjectMonitoring::where('project_id', $project->id)->get();
        foreach ($monitorings as $monitoring) {
            $monitoring->delete();
        }
    }
}

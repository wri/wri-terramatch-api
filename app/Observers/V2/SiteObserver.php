<?php

namespace App\Observers\V2;

use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;

class SiteObserver
{
    public function deleted(Site $site): void
    {
        $this->deleleNestedSiteReports($site);
        $this->deleleNestedSiteMonitorings($site);
    }

    private function deleleNestedSiteReports(Site $site): void
    {
        $reports = SiteReport::where('site_id', $site->id)->get();
        foreach ($reports as $report) {
            $report->delete();
        }
    }

    private function deleleNestedSiteMonitorings(Site $site): void
    {
        $monitorings = SiteMonitoring::where('site_id', $site->id)->get();
        foreach ($monitorings as $monitoring) {
            $monitoring->delete();
        }
    }
}

<?php

namespace App\Http\Controllers\V2\Sites\Monitoring;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\Monitoring\SiteMonitoringResource;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Http\Request;

class ViewSiteMonitoringController extends Controller
{
    public function __invoke(Request $request, SiteMonitoring $siteMonitoring): SiteMonitoringResource
    {
        $this->authorize('read', $siteMonitoring);

        return new SiteMonitoringResource($siteMonitoring);
    }
}

<?php

namespace App\Http\Controllers\V2\Sites\Monitoring;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Sites\Monitoring\UpdateSiteMonitoringRequest;
use App\Http\Resources\V2\Sites\Monitoring\SiteMonitoringResource;
use App\Models\V2\Sites\SiteMonitoring;

class AdminUpdateSiteMonitoringController extends Controller
{
    public function __invoke(UpdateSiteMonitoringRequest $request, SiteMonitoring $siteMonitoring): SiteMonitoringResource
    {
        $data = $request->validated();

        $this->authorize('update', $siteMonitoring);

        $data['last_updated'] = now()->toDateString();

        $siteMonitoring->update($data);

        return new SiteMonitoringResource($siteMonitoring);
    }
}

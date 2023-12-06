<?php

namespace App\Http\Controllers\V2\Sites\Monitoring;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Sites\Monitoring\CreateSiteMonitoringRequest;
use App\Http\Resources\V2\Sites\Monitoring\SiteMonitoringResource;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;

class AdminCreateSiteMonitoringController extends Controller
{
    public function __invoke(CreateSiteMonitoringRequest $request): SiteMonitoringResource
    {
        $data = $request->validated();
        $site = Site::whereUuid($data['site_uuid'])->first();

        $this->authorize('createSiteMonitoring', $site);

        $newSiteMonitoring = $site->monitoring()->create([
            'framework_key' => $site->framework_key,
            'site_id' => $site->id,
            'status' => SiteMonitoring::STATUS_ACTIVE,
            'tree_count' => $data['tree_count'],
            'tree_cover' => $data['tree_cover'],
            'field_tree_count' => $data['field_tree_count'],
            'measurement_date' => $data['measurement_date'],
            'last_updated' => now()->toDateString(),
        ]);

        return new SiteMonitoringResource($newSiteMonitoring);
    }
}

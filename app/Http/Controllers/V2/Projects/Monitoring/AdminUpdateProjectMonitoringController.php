<?php

namespace App\Http\Controllers\V2\Projects\Monitoring;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Projects\Monitoring\UpdateProjectMonitoringRequest;
use App\Http\Resources\V2\Projects\Monitoring\ProjectMonitoringResource;
use App\Models\V2\Projects\ProjectMonitoring;

class AdminUpdateProjectMonitoringController extends Controller
{
    public function __invoke(UpdateProjectMonitoringRequest $request, ProjectMonitoring $projectMonitoring): ProjectMonitoringResource
    {
        $data = $request->validated();

        $this->authorize('update', $projectMonitoring);

        $data['last_updated'] = now()->toDateString();

        $projectMonitoring->update($data);

        return new ProjectMonitoringResource($projectMonitoring);
    }
}

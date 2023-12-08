<?php

namespace App\Http\Controllers\V2\Projects\Monitoring;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Projects\Monitoring\CreateProjectMonitoringRequest;
use App\Http\Resources\V2\Projects\Monitoring\ProjectMonitoringResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;

class AdminCreateProjectMonitoringController extends Controller
{
    public function __invoke(CreateProjectMonitoringRequest $request): ProjectMonitoringResource
    {
        $data = $request->validated();
        $project = Project::whereUuid($data['project_uuid'])->first();

        $this->authorize('createProjectMonitoring', $project);

        $data['project_id'] = $project->id;
        $data['framework_key'] = $project->framework_key;
        $data['status'] = ProjectMonitoring::STATUS_ACTIVE;
        $data['last_updated'] = now()->toDateString();

        $newProjectMonitoring = $project->monitoring()->create($data);

        return new ProjectMonitoringResource($newProjectMonitoring);
    }
}

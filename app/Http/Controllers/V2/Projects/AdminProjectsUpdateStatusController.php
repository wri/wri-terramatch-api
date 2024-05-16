<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use App\Models\Traits\SaveAuditStatusTrait;

class AdminProjectsUpdateStatusController extends Controller
{
    use SaveAuditStatusTrait;

    public function __invoke(Request $request, string $uuid): Project
    {
        $project = Project::where('uuid', $uuid)->first();
        $body = $request->all();
        if (isset($body['status'])) {
            $project['status'] = $body['status'];
            $this->saveAuditStatus('Project', $project->uuid, $body['status'], $body['comment'], $body['type']);
        } else if (isset($body['is_active'])) {
            $this->saveAuditStatus('Project', $project->uuid, $project->status, $body['comment'], $body['type'], $body['is_active']);
        } else {
            $this->saveAuditStatus('Project', $project->uuid, $project->status, $body['comment'], $body['type']);
        }
        $project->update();
        return $project;
    }
}

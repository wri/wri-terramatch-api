<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditStatus\AuditStatus;

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
            AuditStatus::where('entity_uuid', $project->uuid)
                ->where('type', $body['type'])
                ->update(['is_active' => false]);
            $this->saveAuditStatus('Project', $project->uuid, $project->status, $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        }
        $project->update();
        return $project;
    }
}

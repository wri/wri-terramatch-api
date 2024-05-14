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
        $project['status']= $body['status'];
        $project->update();
        $this->saveAuditStatus('Project', $project->uuid, $body['status'], $body['comment']);
        return $project;
    }
}

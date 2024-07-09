<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use Illuminate\Http\Request;

class DeleteProjectMonitoringPartnersController extends Controller
{
    public function __invoke(Request $request, Project $project, string $email)
    {
        $projectUserInvites = ProjectInvite::with('user')
            ->where('project_id', $project->id)
            ->where('email_address', $email)
            ->get();

        $monitoringPartner = $projectUserInvites->first()->user;
        if ($monitoringPartner) {
            $project->users()->detach($monitoringPartner->id);
        }

        $projectUserInvites->each->delete();

        return response()->json(['message' => 'Monitoring Partner successfully removed.'], 200);
    }
}

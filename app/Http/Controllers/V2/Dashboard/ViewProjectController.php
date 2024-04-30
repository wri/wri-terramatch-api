<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\ViewProjectResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use Illuminate\Support\Facades\Auth;

class ViewProjectController extends Controller
{
    public function __invoke(String $uuid): ViewProjectResource
    {
       $user = Auth::user();

        if ($user->primary_role->name == 'admin-super' || $user->primary_role->name == 'admin') {
            $response = (object)[
                'allowed' => true,
            ];
        }
        if ($user->primary_role->name == 'government' && $user->country !== null) {
            $isAllowed = Project::where('uuid', $uuid)
                ->where('country', $user->country)
                ->first();
            $response = (object)[
                'allowed' => boolval($isAllowed),
            ];
        }
        if ($user->primary_role->name == 'funder' && $user->program !== null) {
            $isAllowed = Project::where('uuid', $uuid)
                ->where('framework_key', $user->program)
                ->first();
            $response = (object)[
                'allowed' => boolval($isAllowed),
            ];
        }
        if ($user->primary_role->name == 'project-developer') {
            $projectId = Project::where('uuid', $uuid)
                ->value('id');
            $isInvite = ProjectInvite::where('email_address', $user->email_address)
                ->where('project_id', $projectId)
                ->first();
            $response = (object)[
                'allowed' => boolval($isInvite),
            ];
        }

        return new ViewProjectResource($response);
    }
};

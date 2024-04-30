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
        $role = $user->role;
        if ($role === 'government') {
            $isAllowed = Project::where('uuid', $uuid)
                ->where('country', $user->country)
                ->first();
            $response = (object)[
                'allowed' => $isAllowed ? true : false,
            ];
        } elseif ($role === 'funder') {
            $isAllowed = Project::where('uuid', $uuid)
                ->where('framework', $user->program)
                ->first();
            $response = (object)[
                'allowed' => $isAllowed ? true : false,
            ];
        } elseif ($role === 'project_developer') {
            $projectId = Project::where('uuid', $uuid)
                ->value('id');
            $isInvite = ProjectInvite::where('email_address', $user->email_address)
                ->where('project_id', $projectId)
                ->first();
            $response = (object)[
                'allowed' => $isInvite ? true : false,
            ];
        } elseif ($role === 'admin' || $role === 'terrafund_admin') {
            $response = (object)[
                'allowed' => true,
            ];
        } else {
            $response = (object)[
                'allowed' => false,
            ];
        }

        return new ViewProjectResource($response);
    }
};

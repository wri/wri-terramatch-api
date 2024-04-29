<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Models\V2\Projects\ProjectInvite;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\ViewProjectResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\V2\Projects\Project;

class ViewProjectController extends Controller
{
    public function __invoke(String $uuid): ViewProjectResource
    {
        $user = Auth::user();
        $projectId = Project::where('uuid', $uuid)
            ->value('id');
        $isInvite = ProjectInvite::where('email_address', $user->email_address)
            ->where('project_id', $projectId)->first();
        $response = (object)[
            'allowed' => $isInvite ? true : false,
        ];
        return new ViewProjectResource($response);
    }
};

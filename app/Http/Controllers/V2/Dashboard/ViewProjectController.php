<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\Framework;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use App\Models\V2\User;
use Illuminate\Support\Facades\Auth;

class ViewProjectController extends Controller
{
    public function getIfUserIsAllowedToProject(String $uuid)
    {
        /** @var User $user */
        $user = Auth::user();
        if (is_null($user)) {
            $response = (object)[
              'allowed' => false,
            ];
        } elseif ($user->hasRole('government')) {
            $response = (object)[
              'allowed' => false,
            ];
        } elseif ($user->hasRole('funder')) {
            $verifyInUserProgram = Project::where('uuid', $uuid)
                ->where('framework_key', $user->program)
                ->exists();
            if ($verifyInUserProgram) {
                $isAllowed = $verifyInUserProgram;
            } else {
                $frameworksSlugs = $user->my_frameworks_slug;
                $verifyInUserFrameworks = Project::where('uuid', $uuid)
                    ->whereIn('framework_key', $frameworksSlugs)
                    ->exists();
                $isAllowed = $verifyInUserFrameworks;
            }
            $response = (object)[
                'allowed' => $isAllowed,
            ];
        } elseif ($user->hasRole('project-developer')) {
            $project = Project::where('uuid', $uuid)->first();
            $projectId = $project ? $project->id : null;

            $isInvite = ProjectInvite::where('email_address', $user->email_address)
                ->where('project_id', $projectId)
                ->exists();

            $isAllowedByOrganization = $project && $user->organisation
                && ($user->organisation->id == $project->organisation_id
                    || $user->projects->contains($project->id));

            $response = (object)[
                'allowed' => $isInvite || $isAllowedByOrganization,
            ];
        } elseif ($user->hasDashboardAdminAccess()) {
            $response = (object)[
                'allowed' => true,
            ];
        } else {
            $response = (object)[
                'allowed' => false,
            ];
        }

        return response()->json($response);
    }

    public function getFrameworks($request = null)
    {
        if ($request === null) {
            $request = request();
        }

        $baseQuery = TerrafundDashboardQueryHelper::buildQueryFromRequest($request);

        $frameworkKeys = $baseQuery->distinct()->pluck('framework_key')->toArray();

        $frameworks = Framework::whereIn('slug', $frameworkKeys)
            ->select('name', 'slug')
            ->get();

        $frameworksResponse = [];
        foreach ($frameworks as $framework) {
            $frameworksResponse[] = [
                'framework_slug' => $framework->slug,
                'name' => $framework->name,
            ];
        }

        return $frameworksResponse;
    }
};

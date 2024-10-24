<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\Framework;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewProjectController extends Controller
{
    public function getIfUserIsAllowedToProject(String $uuid)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->hasRole('government')) {
            $isAllowed = Project::where('uuid', $uuid)
                ->where('country', $user->country)
                ->exists();
            $response = (object)[
                'allowed' => $isAllowed,
            ];
        } elseif ($user->hasRole('funder')) {
            $isAllowed = Project::where('uuid', $uuid)
                ->where('framework_key', $user->program)
                ->exists();
            $response = (object)[
                'allowed' => $isAllowed,
            ];
        } elseif ($user->hasRole('project-developer')) {
            $projectId = Project::where('uuid', $uuid)
                ->value('id');
            $isInvite = ProjectInvite::where('email_address', $user->email_address)
                ->where('project_id', $projectId)
                ->exists();
            $response = (object)[
                'allowed' => $isInvite,
            ];
        } elseif ($user->isAdmin) {
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

    public function getAllProjectsAllowedToUser()
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            if ($user->hasAnyRole(['admin-super', 'admin-terrafund'])) {
                $response = TerrafundDashboardQueryHelper::getPolygonsByStatus();

                return response()->json([
                  'polygonsUuids' => $response,
                ]);
            } else {
                if ($user->hasRole('government')) {
                    try {
                        $projectUuids = Project::where('framework_key', 'terrafund')->where('country', $user->country)->pluck('uuid');
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        Log::error('Error fetching projects for government: ' . $errorMessage);

                        return response()->json(['error' => 'An error occurred while fetching government projects', 'message' => $errorMessage], 500);
                    }
                } elseif ($user->hasRole('funder')) {
                    try {
                        $projectUuids = Project::where('framework_key', $user->program)->pluck('uuid');
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        Log::error('Error fetching projects for funder: ' . $errorMessage);

                        return response()->json(['error' => 'An error occurred while fetching funder projects', 'message' => $errorMessage], 500);
                    }
                } elseif ($user->hasRole('project-developer')) {
                    try {
                        $projectIds = ProjectInvite::where('email_address', $user->email_address)->pluck('project_id');
                        $projectUuids = Project::whereIn('id', $projectIds)->where('framework_key', 'terrafund')->pluck('uuid');
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        Log::error('Error fetching projects for project developer: ' . $errorMessage);

                        return response()->json(['error' => 'An error occurred while fetching project developer projects', 'message' => $errorMessage], 500);
                    }
                } else {
                    $projectUuids = null;
                }

                Log::info('Returning this value: ' . json_encode($projectUuids));
                $polygonsData = [
                  'needs-more-information' => [],
                  'submitted' => [],
                  'approved' => [],
                  'draft' => [],
                ];

                foreach ($projectUuids as $uuid) {
                    Log::info('Fetching polygons for project UUID ' . $uuid);
                    $request = new Request(['uuid' => $uuid]);

                    try {
                        $polygonsResource = TerrafundDashboardQueryHelper::getPolygonsByStatusOfProject($request);
                        foreach ($polygonsResource as $status => $polygons) {
                            $polygons = $polygons instanceof \Illuminate\Support\Collection ? $polygons->toArray() : $polygons;
                            $polygonsData[$status] = array_merge($polygonsData[$status], $polygons);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error fetching polygons for project UUID ' . $uuid . ': ' . $e->getMessage());
                    }
                }

                return response()->json([
                  'projectsUuids' => $projectUuids->toArray(),
                  'polygonsUuids' => $polygonsData,
                ]);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error('An error occurred: ' . $errorMessage);

            return response()->json(['error' => 'An error occurred while fetching the data', 'message' => $errorMessage], 500);
        }
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

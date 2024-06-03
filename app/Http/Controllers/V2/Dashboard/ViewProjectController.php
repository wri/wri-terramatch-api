<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\ViewProjectResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewProjectController extends Controller
{
    public function getIfUserIsAllowedToProject(String $uuid): ViewProjectResource
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
                ->where('framework_key', $user->program)
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

    public function getAllProjectsAllowedToUser()
    {
        try {
            $user = Auth::user();
            $role = $user->role;
            Log::info($role);
            if ($role === 'admin' || $role === 'terrafund_admin' || $role === 'terrafund-admin') {
                $response = TerrafundDashboardQueryHelper::getPolygonsByStatus();

                return response()->json([
                  'polygonsUuids' => $response,
                ]);
            } else {
                if ($role === 'government') {
                    try {
                        $projectUuids = Project::where('framework_key', 'terrafund')->where('country', $user->country)->pluck('uuid');
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        Log::error('Error fetching projects for government: ' . $errorMessage);

                        return response()->json(['error' => 'An error occurred while fetching government projects', 'message' => $errorMessage], 500);
                    }
                } elseif ($role === 'funder') {
                    try {
                        $projectUuids = Project::where('framework_key', $user->program)->pluck('uuid');
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        Log::error('Error fetching projects for funder: ' . $errorMessage);

                        return response()->json(['error' => 'An error occurred while fetching funder projects', 'message' => $errorMessage], 500);
                    }
                } elseif ($role === 'project_developer') {
                    try {
                        $projectIds = ProjectInvite::where('email_address', $user->email_address)->pluck('project_id');
                        $projectUuids = Project::whereIn('id', $projectIds)->where('framework_key', 'terrafund')->pluck('uuid');
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        Log::error('Error fetching projects for project developer: ' . $errorMessage);

                        return response()->json(['error' => 'An error occurred while fetching project developer projects', 'message' => $errorMessage], 500);
                    }
                } elseif ($role === 'admin' || $role === 'terrafund_admin' || $role === 'terrafund-admin') {

                } else {
                    $projectUuids = null;
                }

                Log::info('Returning this value: ' . json_encode($projectUuids));
                $polygonsData = [
                  'needs-more-information' => [],
                  'submitted' => [],
                  'approved' => [],
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
};

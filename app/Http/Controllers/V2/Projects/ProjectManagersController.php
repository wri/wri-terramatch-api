<?php

namespace App\Http\Controllers\V2\Projects;

use App\Helpers\ErrorHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Projects\AddProjectManagerRequest;
use App\Http\Resources\V2\User\AssociatedUserResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Http\JsonResponse;

class ProjectManagersController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('read', $project);

        return AssociatedUserResource::collection($project->managers);
    }

    public function store(Project $project, AddProjectManagerRequest $request)
    {
        $this->authorize('update', $project);

        $user = User::where('email_address', $request->get('email_address'))->first();
        if (empty($user)) {
            return $this->errorResponse('email address', 'not found', 404);
        }

        if ($user->primaryRole?->name != 'project-manager') {
            return $this->errorResponse('user', 'is not a project manager');
        }

        if ($user->projects()->wherePivot('is_managing', true)->isUuid($project->uuid)->exists()) {
            return $this->errorResponse('user', 'is already a project manager for this project');
        }

        $user->projects()->sync([$project->id => ['is_managing' => true]], false);

        return new AssociatedUserResource($user);
    }

    public function destroy(Project $project, string $userUuid)
    {
        $this->authorize('update', $project);

        $user = User::isUuid($userUuid)->first();
        if (empty($user)) {
            return $this->errorResponse('user', 'was not found', 404);
        }

        if (! $project->managers()->where('uuid', $user->uuid)->exists()) {
            return $this->errorResponse('user', 'is not a project manager for this project');
        }

        $project->managers()->detach($user->id);

        return response()->json();
    }

    protected function errorResponse(string $pretty, string $message, $code = 422): JsonResponse
    {
        $errors = ErrorHelper::create('*', $pretty, 'CUSTOM', $message);

        return JsonResponseHelper::error($errors, $code);
    }
}

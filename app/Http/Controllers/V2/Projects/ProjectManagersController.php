<?php

namespace App\Http\Controllers\V2\Projects;

use App\Helpers\ErrorHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Projects\AddProjectManagerRequest;
use App\Http\Resources\V2\User\AssociatedUserResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;

class ProjectManagersController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('read', $project);
    }

    public function store(Project $project, AddProjectManagerRequest $request)
    {
        $this->authorize('update', $project);

        $user = User::where('email_address', $request->validated()->get('email_address'))->first();
        if (empty($user)) {
            return $this->errorResponse('email address', 'not found', 404);
        }

        if ($user->primaryRole?->name != 'project-manager') {
            return $this->errorReponse('user', 'is not a project manager');
        }

        $user->projects()->sync([$project->id => ['is_managing' => true]], false);

        return new AssociatedUserResource($user);
    }

    public function destroy(Project $project, User $user)
    {
        $this->authorize('update', $project);
    }

    protected function errorResponse(string $pretty, string $message, $code = 422)
    {
        $errors = ErrorHelper::create('*', $pretty, 'CUSTOM', $message);
        return JsonResponseHelper::error($errors, $code);
    }
}

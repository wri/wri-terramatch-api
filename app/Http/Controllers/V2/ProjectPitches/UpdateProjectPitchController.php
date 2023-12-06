<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ProjectPitches\UpdateProjectPitchRequest;
use App\Http\Resources\V2\ProjectPitches\ProjectPitchResource;
use App\Models\V2\ProjectPitch;

class UpdateProjectPitchController extends Controller
{
    public function __invoke(ProjectPitch $projectPitch, UpdateProjectPitchRequest $updateProjectPitchRequest): ProjectPitchResource
    {
        $this->authorize('read', $projectPitch->organisation);
        $projectPitch->update($updateProjectPitchRequest->validated());
        $projectPitch->save();

        if ($updateProjectPitchRequest->get('tags')) {
            $projectPitch->syncTags($updateProjectPitchRequest->get('tags'));
        }

        return new ProjectPitchResource($projectPitch);
    }
}

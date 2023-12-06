<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectPitches\ProjectPitchResource;
use App\Models\V2\ProjectPitch;

class DeleteProjectPitchController extends Controller
{
    public function __invoke(ProjectPitch $projectPitch): ProjectPitchResource
    {
        $this->authorize('read', $projectPitch->organisation);
        $projectPitch->delete();

        return new ProjectPitchResource($projectPitch);
    }
}

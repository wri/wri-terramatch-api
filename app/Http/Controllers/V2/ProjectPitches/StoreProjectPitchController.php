<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ProjectPitches\StoreProjectPitchRequest;
use App\Http\Resources\V2\ProjectPitches\ProjectPitchResource;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;

class StoreProjectPitchController extends Controller
{
    public function __invoke(StoreProjectPitchRequest $storeProjectPitchRequest): ProjectPitchResource
    {
        $organisation = Organisation::where('uuid', $storeProjectPitchRequest->get('organisation_id'))->firstOrFail();
        $this->authorize('read', $organisation);
        $projectPitch = ProjectPitch::create($storeProjectPitchRequest->validated());

        if ($storeProjectPitchRequest->get('tags')) {
            $projectPitch->syncTags($storeProjectPitchRequest->get('tags'));
        }

        return new ProjectPitchResource($projectPitch);
    }
}

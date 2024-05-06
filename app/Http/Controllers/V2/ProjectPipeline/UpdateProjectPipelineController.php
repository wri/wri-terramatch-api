<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateProjectPipelineRequest;
use App\Http\Resources\V2\ProjectPipelineResource;
use App\Models\V2\ProjectPipeline;

class UpdateProjectPipelineController extends Controller
{
    public function __invoke(UpdateProjectPipelineRequest $updateProjectPipelineRequest, String $id): ProjectPipelineResource
    {
        $validatedData = $updateProjectPipelineRequest->validated();
        $projectPipeline = ProjectPipeline::findOrFail($id);
        $projectPipeline->update($validatedData);

        return new ProjectPipelineResource($projectPipeline);
    }
}

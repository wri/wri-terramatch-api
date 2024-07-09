<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ProjectPipelineRequest;
use App\Http\Resources\V2\ProjectPipelineResource;
use App\Models\V2\ProjectPipeline;

class UpdateProjectPipelineController extends Controller
{
    public function __invoke(ProjectPipelineRequest $updateProjectPipelineRequest, String $id): ProjectPipelineResource
    {
        $requestUpdateData = $updateProjectPipelineRequest->all();
        $projectPipeline = ProjectPipeline::findOrFail($id);
        $projectPipeline->update($requestUpdateData);

        return new ProjectPipelineResource($projectPipeline);
    }
}

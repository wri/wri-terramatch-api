<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ProjectPipelineRequest;
use App\Http\Resources\V2\ProjectPipelineResource;
use App\Models\V2\ProjectPipeline;

class StoreProjectPipelineController extends Controller
{
    public function __invoke(ProjectPipelineRequest $storeProjectPipelineRequest): ProjectPipelineResource
    {
        $projectPipeline = ProjectPipeline::create($storeProjectPipelineRequest->all());

        return new ProjectPipelineResource($projectPipeline);
    }
}

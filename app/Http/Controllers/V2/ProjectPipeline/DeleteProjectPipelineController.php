<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectPipelineResource;
use App\Models\V2\ProjectPipeline;

class DeleteProjectPipelineController extends Controller
{
    public function __invoke(ProjectPipeline $projectPipeline, String $id): ProjectPipelineResource
    {
        ProjectPipeline::where('id', $id)->delete();
        return new ProjectPipelineResource($projectPipeline);
    }
}

<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectPipelineResource;
use App\Models\V2\ProjectPipeline;

class GetProjectPipelineController extends Controller
{
    public function __invoke(string $id = null)
    {
        if ($id != null) {
            $projectsPipeline = ProjectPipeline::where('id', $id)->first();

            return new ProjectPipelineResource($projectsPipeline);
        } else {
            $projectsPipeline = ProjectPipeline::orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return ProjectPipelineResource::collection($projectsPipeline);
        }
    }
}

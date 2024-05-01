<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use App\Models\V2\ProjectPipeline;
use App\Http\Resources\V2\ProjectPipelineResource;
use Illuminate\Support\Facades\Log;

class GetProjectPipelineController extends Controller
{
    public function __invoke(string $id = null)
    {
        if ($id != null) {
            $projectsPipeline = ProjectPipeline::where('id', $id)->first();
            return new ProjectPipelineResource($projectsPipeline);
        } else {
            $projectsPipeline = ProjectPipeline::all();
            return ProjectPipelineResource::collection($projectsPipeline);
        }
    }
}

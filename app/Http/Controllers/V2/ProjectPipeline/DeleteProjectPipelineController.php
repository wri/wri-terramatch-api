<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use App\Models\V2\ProjectPipeline;
use Illuminate\Http\JsonResponse;

class DeleteProjectPipelineController extends Controller
{
    public function __invoke(String $id): JsonResponse
    {
        $projectPipeline = ProjectPipeline::findOrFail($id);

        $projectPipeline->delete();

        return response()->json(['message' => 'Project pipeline deleted successfully.'], 200);
    }
}

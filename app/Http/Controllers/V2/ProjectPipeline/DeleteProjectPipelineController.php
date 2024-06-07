<?php

namespace App\Http\Controllers\V2\ProjectPipeline;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\V2\ProjectPipeline;

class DeleteProjectPipelineController extends Controller
{
    public function __invoke(String $id): JsonResponse
    {
        $projectPipeline = ProjectPipeline::findOrFail($id);

        $projectPipeline->delete();

        return response()->json(['message' => 'Project pipeline deleted successfully.'], 200);
    }
}

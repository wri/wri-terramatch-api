<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Support\Facades\Log;
use Exception;

class EntityTypeController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $project = Project::where('uuid', $uuid)->first();
            if ($project) {
                return response()->json([
                    'type' => 'project',
                    'uuid' => $uuid
                ]);
            }

            $site = Site::where('uuid', $uuid)->first();
            if ($site) {
                return response()->json([
                    'type' => 'site',
                    'uuid' => $uuid
                ]);
            }

            return response()->json([
                'type' => 'unknown',
                'uuid' => $uuid
            ]);
        } catch (Exception $e) {

            Log::error($e);

            return response()->json([
                'error' => 'An error occurred while processing your request.'
            ], 500);
        }
    }
}

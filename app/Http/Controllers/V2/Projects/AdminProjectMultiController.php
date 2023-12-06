<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectsCollection;
use App\Models\V2\Projects\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProjectMultiController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('readAll', Project::class);

        if (! empty($request->query('ids'))) {
            $collection = Project::whereIn('uuid', explode(',', $request->query('ids')))->get();

            if ($collection->count() > 0) {
                return new ProjectsCollection($collection);
            }

            return new JsonResponse('No records found.', 404);
        }

        return new JsonResponse('No uuids provided.', 406);
    }
}

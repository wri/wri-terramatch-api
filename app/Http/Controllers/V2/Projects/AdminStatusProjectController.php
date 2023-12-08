<?php

namespace App\Http\Controllers\V2\Projects;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateRequests\StatusChangeRequest;
use App\Http\Resources\V2\Projects\ProjectResource;
use App\Models\V2\Projects\Project;
use Illuminate\Http\JsonResponse;

class AdminStatusProjectController extends Controller
{
    public function __invoke(StatusChangeRequest $request, Project $project, string $status)
    {
        $data = $request->validated();
        $this->authorize('approve', $project);

        switch($status) {
            case 'approve':
                $project->update(['status' => Project::STATUS_APPROVED,
                    'feedback' => data_get($data, 'feedback'),
                ]);

                break;
            case 'moreinfo':
                $project->update([
                    'status' => Project::STATUS_NEEDS_MORE_INFORMATION,
                    'feedback' => data_get($data, 'feedback'),
                    'feedback_fields' => data_get($data, 'feedback_fields'),
                ]);

                break;
            default:
                return new JsonResponse('status not supported', 401);
        }

        EntityStatusChangeEvent::dispatch($request->user(), $project, $project->name, '', $project->readable_status);

        return new ProjectResource($project);
    }
}

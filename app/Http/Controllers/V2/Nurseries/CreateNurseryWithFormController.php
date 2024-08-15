<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\CreateEntityFormRequest;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class CreateNurseryWithFormController extends Controller
{
    public function __invoke(CreateEntityFormRequest $formRequest)
    {
        $data = $formRequest->validated();

        $project = Project::isUuid(data_get($data, 'parent_uuid'))->first();
        $this->authorize('createNurseries', $project);

        if (empty($project)) {
            return new JsonResponse('No Project found for this nursery.', 404);
        }

        $nursery = Nursery::create([
            'framework_key' => $project->framework_key,
            'project_id' => $project->id,
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $lastTask = $project->tasks()->orderby('due_at', 'desc')->first();

        if ($lastTask) {
            $nextReportingPeriod = Carbon::parse($lastTask->due_at)->addWeeks(4);
            $creationDate = Carbon::now();
            $weeksDifference = $creationDate->diffInWeeks($nextReportingPeriod);

            if ($weeksDifference > 4) {
                $lastTask->nurseryReports()->create([
                    'framework_key' => $project->framework_key,
                    'nursery_id' => $nursery->id,
                    'status' => 'due',
                    'due_at' => $lastTask->due_at,
                ]);

            }
        }

        return $nursery->createSchemaResource();
    }
}

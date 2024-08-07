<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\CreateEntityFormRequest;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Http\JsonResponse;
use App\Models\V2\Tasks\Task;
use Illuminate\Support\Carbon;

class CreateSiteWithFormController extends Controller
{
    public function __invoke(Form $form, CreateEntityFormRequest $formRequest)
    {
        $data = $formRequest->validated();

        $project = Project::isUuid(data_get($data, 'parent_uuid'))->first();
        $this->authorize('createNurseries', $project);

        if (empty($project)) {
            return new JsonResponse('No Project found for this site.', 404);
        }

        $site = Site::create([
            'framework_key' => $project->framework_key,
            'project_id' => $project->id,
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $lastTask = Task::where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastTask) {
            $nextReportDueDate = Carbon::parse($lastTask->due_at)->addWeeks(4);

            if (Carbon::now()->lessThan($nextReportDueDate)) {
                SiteReport::create([
                    'framework_key' => $lastTask->project->framework_key,
                    'task_id' => $lastTask->id,
                    'site_id' => $site->id,
                    'status' => 'due',
                    'due_at' => $lastTask->due_at,
                ]);

            }
        }

        return $site->createSchemaResource();
    }
}

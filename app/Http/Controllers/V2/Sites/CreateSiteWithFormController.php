<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\CreateEntityFormRequest;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\StateMachines\SiteStatusStateMachine;
use Illuminate\Http\JsonResponse;

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
            'status' => SiteStatusStateMachine::DRAFT,
        ]);

        return $site->createSchemaResource();
    }
}

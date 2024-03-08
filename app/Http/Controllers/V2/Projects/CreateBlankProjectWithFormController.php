<?php

namespace App\Http\Controllers\V2\Projects;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Entities\EntityWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Http\Request;

class CreateBlankProjectWithFormController extends Controller
{
    public function __invoke(Request $request, Form $form): EntityWithSchemaResource
    {
        $this->authorize('create', Project::class);

        $organizationId = $request->user()->organisation_id;

        $project = Project::create([
            'framework_key' => $form->framework_key,
            'organisation_id' => $organizationId,
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        EntityStatusChangeEvent::dispatch($request->user(), $project, '', '', $project->readable_status);

        return $project->createSchemaResource();
    }
}

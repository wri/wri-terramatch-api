<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\CreateEntityFormRequest;
use App\Http\Resources\V2\Nurseries\NurseyWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use Illuminate\Http\JsonResponse;

class CreateNurseryWithFormController extends Controller
{
    public function __invoke(CreateEntityFormRequest $formRequest)
    {
        $data = $formRequest->validated();

        $project = Project::isUuid(data_get($data, 'parent_uuid'))->first();
        $this->authorize('createNurseries', $project);

        $form = $this->getForm($data, $project);

        if (empty($project)) {
            return new JsonResponse('No Project found for this nursery.', 404);
        }

        $nursery = Nursery::create([
            'framework_key' => $project->framework_key,
            'project_id' => $project->id,
            'status' => Nursery::STATUS_STARTED,
        ]);

        return new NurseyWithSchemaResource($nursery, ['schema' => $form]);
    }

    private function getForm(array $data, Project $project): Form
    {
        return Form::where('framework_key', $project->framework_key)
            ->where('model', Nursery::class)
            ->first();
    }
}

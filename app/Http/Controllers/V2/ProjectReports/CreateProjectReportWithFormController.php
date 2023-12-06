<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\CreateEntityFormRequest;
use App\Http\Resources\V2\ProjectReports\ProjectReportWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CreateProjectReportWithFormController extends Controller
{
    public function __invoke(CreateEntityFormRequest $formRequest)
    {
        $data = $formRequest->validated();

        $project = Project::isUuid(data_get($data, 'parent_uuid'))->first();
        $this->authorize('createReport', $project);

        $form = $this->getForm($data, $project);

        if (empty($project)) {
            return new JsonResponse('No Project found for this report.', 404);
        }

        $report = ProjectReport::create([
            'framework_key' => $project->framework_key,
            'project_id' => $project->id,
            'status' => ProjectReport::STATUS_STARTED,
            'created_by' => Auth::user()->id,
        ]);

        return new ProjectReportWithSchemaResource($report, ['schema' => $form]);
    }

    private function getForm(array $data, Project $project): Form
    {
        return Form::where('framework_key', $project->framework_key)
            ->where('model', ProjectReport::class)
            ->first();
    }
}

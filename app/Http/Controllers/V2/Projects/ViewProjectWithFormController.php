<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ViewProjectWithFormController extends Controller
{
    public function __invoke(Request $request, Project $project): ProjectWithSchemaResource
    {
        $this->authorize('read', $project);

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        $schema = Form::where('framework_key', $project->framework_key)
            ->where('model', Project::class)
            ->first();

        return new ProjectWithSchemaResource($project, ['schema' => $schema]);
    }
}

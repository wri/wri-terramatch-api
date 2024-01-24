<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectReports\ProjectReportWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ViewProjectReportWithFormController extends Controller
{
    public function __invoke(Request $request, ProjectReport $projectReport): ProjectReportWithSchemaResource
    {
        $this->authorize('read', $projectReport);

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        $schema = Form::where('framework_key', $projectReport->framework_key)
            ->where('model', ProjectReport::class)
            ->first();

        return new ProjectReportWithSchemaResource($projectReport, ['schema' => $schema]);
    }
}

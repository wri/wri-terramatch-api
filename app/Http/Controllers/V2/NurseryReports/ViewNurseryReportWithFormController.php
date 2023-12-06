<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\NurseryReports\NurseryReportWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\Request;

class ViewNurseryReportWithFormController extends Controller
{
    public function __invoke(Request $request, NurseryReport $nurseryReport): NurseryReportWithSchemaResource
    {
        $this->authorize('read', $nurseryReport);

        $schema = Form::where('framework_key', $nurseryReport->framework_key)
            ->where('model', NurseryReport::class)
            ->first();

        return new NurseryReportWithSchemaResource($nurseryReport, ['schema' => $schema]);
    }
}

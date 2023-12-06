<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\CreateEntityFormRequest;
use App\Http\Resources\V2\NurseryReports\NurseryReportWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CreateNurseryReportWithFormController extends Controller
{
    public function __invoke(Form $form, CreateEntityFormRequest $formRequest)
    {
        $data = $formRequest->validated();

        $nursery = Nursery::isUuid(data_get($data, 'parent_uuid'))->first();
        $this->authorize('createReport', $nursery);

        if (empty($nursery)) {
            return new JsonResponse('No Nursery found for this report.', 404);
        }

        $form = $this->getForm($data, $nursery);

        $report = NurseryReport::create([
            'framework_key' => $nursery->framework_key,
            'nursery_id' => $nursery->id,
            'status' => NurseryReport::STATUS_STARTED,
            'created_by' => Auth::user()->id,
        ]);

        return new NurseryReportWithSchemaResource($report, ['schema' => $form]);
    }

    private function getForm(array $data, Nursery $nursery): Form
    {
        return Form::where('framework_key', $nursery->framework_key)
            ->where('model', NurseryReport::class)
            ->first();
    }
}

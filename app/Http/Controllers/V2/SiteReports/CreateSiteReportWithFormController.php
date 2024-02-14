<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Forms\CreateEntityFormRequest;
use App\Http\Resources\V2\SiteReports\SiteReportWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

// TODO (NJC): Creating reports with a form is not valid, and will be removed from the API in a future ticket.
class CreateSiteReportWithFormController extends Controller
{
    public function __invoke(Form $form, CreateEntityFormRequest $formRequest)
    {
        $data = $formRequest->validated();

        $site = Site::isUuid(data_get($data, 'parent_uuid'))->first();
        $this->authorize('createReport', $site);

        if (empty($site)) {
            return new JsonResponse('No Site found for this report.', 404);
        }

        $form = $this->getForm($data, $site);

        $report = SiteReport::create([
            'framework_key' => $site->framework_key,
            'site_id' => $site->id,
            'status' => SiteReport::STATUS_STARTED,
            'created_by' => Auth::user()->id,
        ]);

        return new SiteReportWithSchemaResource($report, ['schema' => $form]);
    }

    private function getForm(array $data, Site $site): Form
    {
        return Form::where('framework_key', $site->framework_key)
            ->where('model', SiteReport::class)
            ->first();
    }
}

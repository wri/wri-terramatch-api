<?php

namespace App\Http\Controllers\V2\ReportingFrameworks;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ReportingFrameworks\UpdateReportingFrameworkRequest;
use App\Http\Resources\V2\ReportingFrameworks\ReportingFrameworkResource;
use App\Models\Framework;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;

class AdminUpdateReportingFrameworkController extends Controller
{
    public function __invoke(Framework $framework, UpdateReportingFrameworkRequest $frameworkRequest): ReportingFrameworkResource
    {
        $this->authorize('update', $framework);
        $framework->update($frameworkRequest->validated());
        $framework->save();

        Form::isUuid($frameworkRequest->project_form_uuid)->update([
            'framework_key' => $framework->slug,
            'model' => Project::class,
        ]);
        Form::isUuid($frameworkRequest->project_report_form_uuid)->update([
            'framework_key' => $framework->slug,
            'model' => ProjectReport::class,
        ]);
        Form::isUuid($frameworkRequest->site_form_uuid)->update([
            'framework_key' => $framework->slug,
            'model' => Site::class,
        ]);
        Form::isUuid($frameworkRequest->site_report_form_uuid)->update([
            'framework_key' => $framework->slug,
            'model' => SiteReport::class,
        ]);
        Form::isUuid($frameworkRequest->nursery_form_uuid)->update([
            'framework_key' => $framework->slug,
            'model' => Nursery::class,
        ]);
        Form::isUuid($frameworkRequest->nursery_report_form_uuid)->update([
            'framework_key' => $framework->slug,
            'model' => NurseryReport::class,
        ]);

        $this->detachOldForms($framework);

        return new ReportingFrameworkResource($framework);
    }

    private function detachOldForms(Framework $framework)
    {
        $fields = ['project_form_uuid', 'project_report_form_uuid', 'site_form_uuid', 'site_report_form_uuid', 'nursery_form_uuid', 'nursery_report_form_uuid'];

        $uuids = [];
        foreach ($fields as $field) {
            if (! empty($framework->$field)) {
                $uuids[] = $framework->$field;
            }
        }

        $toClear = Form::whereNotIn('uuid', $uuids)
            ->where('framework_key', $framework->slug)
            ->get();

        foreach ($toClear as $record) {
            $record->framework_key = null;
            $record->model = null;
            $record->save();
        }
    }
}

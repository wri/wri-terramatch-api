<?php

namespace App\Http\Controllers\V2\ReportingFrameworks;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ReportingFrameworks\CreateReportingFrameworkRequest;
use App\Http\Resources\V2\ReportingFrameworks\ReportingFrameworkResource;
use App\Models\Framework;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class AdminCreateReportingFrameworkController extends Controller
{
    public function __invoke(CreateReportingFrameworkRequest $frameworkRequest): ReportingFrameworkResource
    {

        // $this->authorize('create', Framework::class);

        $framework = Framework::create([
            'name' => $frameworkRequest->name,
            'slug' => Str::slug($frameworkRequest->name),
            'access_code' => $frameworkRequest->access_code ? $frameworkRequest->access_code : null,
            'project_form_uuid' => $frameworkRequest->project_form_uuid ? $frameworkRequest->project_form_uuid : null,
            'project_report_form_uuid' => $frameworkRequest->project_report_form_uuid ? $frameworkRequest->project_report_form_uuid : null,
            'site_form_uuid' => $frameworkRequest->site_form_uuid ? $frameworkRequest->site_form_uuid : null,
            'site_report_form_uuid' => $frameworkRequest->site_report_form_uuid ? $frameworkRequest->site_report_form_uuid : null,
            'nursery_form_uuid' => $frameworkRequest->nursery_form_uuid ? $frameworkRequest->nursery_form_uuid : null,
            'nursery_report_form_uuid' => $frameworkRequest->nursery_report_form_uuid ? $frameworkRequest->nursery_report_form_uuid : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permissionName = 'framework-' . Str::slug($frameworkRequest->name);
        if (! Permission::where('name', $permissionName)->exists()) {
            $PermissionAdded = Permission::create([
                'name' => 'framework-' . Str::slug($frameworkRequest->name),
                'guard_name' => 'api',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $adminSuperRoleId = 1;
            $adminTerrafund = 3;

            DB::table('role_has_permissions')->insert([
                ['permission_id' => $PermissionAdded->id, 'role_id' => $adminSuperRoleId],
                ['permission_id' => $PermissionAdded->id, 'role_id' => $adminTerrafund],
            ]);
        }

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


        Artisan::call('cache:clear');

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

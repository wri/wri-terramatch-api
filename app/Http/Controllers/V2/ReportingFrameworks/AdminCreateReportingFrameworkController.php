<?php


namespace App\Http\Controllers\V2\ReportingFrameworks;


use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ReportingFrameworks\CreateReportingFrameworkRequest;
use App\Http\Resources\V2\ReportingFrameworks\ReportingFrameworkResource;
use App\Models\Framework;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;


class AdminCreateReportingFrameworkController extends Controller {
   public function __invoke(CreateReportingFrameworkRequest $frameworkRequest): ReportingFrameworkResource {

       $framework = Framework::create([
           'name' => $frameworkRequest->name,
           'slug' => Str::slug($frameworkRequest->name),
           'access_code' => $frameworkRequest->access_code ? $frameworkRequest->access_code :null,
           'project_form_uuid' => $frameworkRequest->project_form_uuid ? $frameworkRequest->project_form_uuid :null,
           'project_report_form_uuid' => $frameworkRequest->project_report_form_uuid ? $frameworkRequest->project_report_form_uuid :null,
           'site_form_uuid' => $frameworkRequest->site_form_uuid ? $frameworkRequest->site_form_uuid :null,
           'site_report_form_uuid' => $frameworkRequest->site_report_form_uuid ? $frameworkRequest->site_report_form_uuid :null,
           'nursery_form_uuid' => $frameworkRequest->nursery_form_uuid ? $frameworkRequest->nursery_form_uuid :null,
           'nursery_report_form_uuid' => $frameworkRequest->nursery_report_form_uuid ? $frameworkRequest->nursery_report_form_uuid :null,
           'created_at' => now(),
           'updated_at' => now(),
       ]);

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

       Artisan::call('cache:clear');

       return new ReportingFrameworkResource($framework);
   }
}

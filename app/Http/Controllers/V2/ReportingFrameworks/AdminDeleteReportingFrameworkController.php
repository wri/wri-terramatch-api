<?php

namespace App\Http\Controllers\V2\ReportingFrameworks;

use App\Http\Controllers\Controller;
use App\Models\Framework;
use App\Models\V2\Forms\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class AdminDeleteReportingFrameworkController extends Controller
{
    public function __invoke(String $uuid)
    {
        // Remove Framework
        $frameworkToDelete = Framework::where('uuid', $uuid)->first();
        Framework::where('uuid', $uuid)->delete();

        // Remove Permission
        $permissionToDelete = Permission::where('name', 'framework-' . Str::slug($frameworkToDelete->name))->first();
        Permission::where('name', 'framework-' . Str::slug($frameworkToDelete->name))->delete();

        // Remove role_has_permissions entries
        DB::table('role_has_permissions')->where('permission_id', $permissionToDelete->id)->delete();

        Form::isUuid($frameworkToDelete->project_form_uuid)->update([
            'framework_key' => null,
            'model' => null,
        ]);
        Form::isUuid($frameworkToDelete->project_report_form_uuid)->update([
            'framework_key' => null,
            'model' => null,
        ]);
        Form::isUuid($frameworkToDelete->site_form_uuid)->update([
            'framework_key' => null,
            'model' => null,
        ]);
        Form::isUuid($frameworkToDelete->site_report_form_uuid)->update([
            'framework_key' => null,
            'model' => null,
        ]);
        Form::isUuid($frameworkToDelete->nursery_form_uuid)->update([
            'framework_key' => null,
            'model' => null,
        ]);
        Form::isUuid($frameworkToDelete->nursery_report_form_uuid)->update([
            'framework_key' => null,
            'model' => null,
        ]);

        Log::info("Removed $frameworkToDelete->name reporting framework $uuid.");

        return response()->json(['message' => 'Record removed successfully.']);
    }
}

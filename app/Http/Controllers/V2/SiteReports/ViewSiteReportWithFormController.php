<?php

namespace App\Http\Controllers\V2\SiteReports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SiteReports\SiteReportWithSchemaResource;
use App\Models\V2\Forms\Form;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ViewSiteReportWithFormController extends Controller
{
    public function __invoke(Request $request, SiteReport $siteReport): SiteReportWithSchemaResource
    {
        $this->authorize('read', $siteReport);

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        $schema = Form::where('framework_key', $siteReport->framework_key)
            ->where('model', SiteReport::class)
            ->first();

        return new SiteReportWithSchemaResource($siteReport, ['schema' => $schema]);
    }
}

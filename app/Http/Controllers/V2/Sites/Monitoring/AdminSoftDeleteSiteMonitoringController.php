<?php

namespace App\Http\Controllers\V2\Sites\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSoftDeleteSiteMonitoringController extends Controller
{
    public function __invoke(Request $request, SiteMonitoring $siteMonitoring): JsonResponse
    {
        $this->authorize('delete', $siteMonitoring);

        $siteMonitoring->delete();

        return new JsonResponse('Site monitoring successfully deleted', 200);
    }
}

<?php

namespace App\Http\Controllers\V2\Projects\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\ProjectMonitoring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSoftDeleteProjectMonitoringController extends Controller
{
    public function __invoke(Request $request, ProjectMonitoring $projectMonitoring): JsonResponse
    {
        $this->authorize('delete', $projectMonitoring);

        $projectMonitoring->delete();

        return new JsonResponse('Project monitoring successfully deleted', 200);
    }
}
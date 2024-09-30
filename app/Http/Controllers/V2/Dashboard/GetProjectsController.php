<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetProjectsController extends Controller
{
    public function __invoke(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->whereNotNull('long')
            ->whereNotNull('lat')
            ->select('v2_projects.uuid', 'long', 'lat', 'v2_projects.name')
            ->get();

        return response()->json(['data' => $projects]);
    }
};

<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use Illuminate\Http\Request;

class RunProjectsService
{
    public function runProjectsJob(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->whereNotNull('long')
            ->whereNotNull('lat')
            ->select('v2_projects.uuid', 'long', 'lat', 'v2_projects.name', 'organisations.type')
            ->get();
        $minLong = $projects->min('long');
        $maxLong = $projects->max('long');
        $minLat = $projects->min('lat');
        $maxLat = $projects->max('lat');

        $bbox = [$minLong, $minLat, $maxLong, $maxLat];

        return [
            'data' => $projects,
            'bbox' => $bbox,
        ];
    }
}

<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetProjectsController extends Controller
{
    public function __invoke(Request $request)
    {
        $frameworks = data_get($request, 'filter.programmes', []);
        $landscapes = data_get($request, 'filter.landscapes', []);
        $organisations = data_get($request, 'filter.organisationType', []);
        $country = data_get($request, 'filter.country', '');
        $uuid = data_get($request, 'filter.projectUuid', '');
        $request = new Request([
          'filter' => [
              'country' => $country,
              'programmes' => $frameworks,
              'landscapes' => $landscapes,
              'organisationType' => $organisations,
              'projectUuid' => $uuid,
          ],
        ]);

        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->whereNotNull('long')
            ->whereNotNull('lat')
            ->select('v2_projects.uuid', 'long', 'lat', 'v2_projects.name')
            ->get();

        return response()->json(['data' => $projects]);
    }
};

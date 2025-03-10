<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetProjectsController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (is_null($user)) {
            $request = new Request(['filter' => []]);
        } elseif ($user->hasRole('government') && data_get($request, 'filter.projectUuid', '')) {
            $request = new Request(['filter' => []]);
        } else {
            $frameworks = data_get($request, 'filter.programmes', []);
            $landscapes = data_get($request, 'filter.landscapes', []);
            $organisations = data_get($request, 'filter.organisationType', []);
            $country = data_get($request, 'filter.country', '');
            $cohort = data_get($request, 'filter.cohort', '');
            $uuid = data_get($request, 'filter.projectUuid', '');

            $request = new Request([
                'filter' => [
                    'country' => $country,
                    'programmes' => $frameworks,
                    'landscapes' => $landscapes,
                    'organisationType' => $organisations,
                    'projectUuid' => $uuid,
                    'cohort' => $cohort,
                ],
            ]);
        }

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
        
        return response()->json([
            'data' => $projects,
            'bbox' => $bbox
        ]);
    }
};

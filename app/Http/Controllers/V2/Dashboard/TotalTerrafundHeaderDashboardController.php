<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Request;

class TotalTerrafundHeaderDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->with([
                'organisation', 
                'reports', 
                'sitePolygons'
            ])
            ->get();

        $countryName = '';
        if ($country = data_get($request, 'filter.country')) {
            $countryName = WorldCountryGeneralized::where('iso', $country)->value('country');
        }

        $response = (object)[
            'total_entries' => $this->getTotalJobsCreatedSum($projects),
            'total_hectares_restored' => round($this->getTotalHectaresSum($projects)),
            'total_trees_restored' => $this->getTotalTreesRestoredSum($projects),
            'total_trees_restored_goal' => $this->getTotalTreesGrownGoalSum($projects),
            'country_name' => $countryName,
        ];

        return response()->json($response);
    }

    public function getTotalDataForCountry(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();
        $countryName = '';
        if ($country = data_get($request, 'filter.country')) {
            $countryName = WorldCountryGeneralized::where('iso', $country)->first()->country;
        }
        $response = (object)[
            'total_entries' => $this->getTotalJobsCreatedSum($projects),
            'total_hectares_restored' => round($this->getTotalHectaresSum($projects)),
            'total_trees_restored' => $this->getTotalTreesRestoredSum($projects),
            'country_name' => $countryName,
        ];

        return response()->json($response);
    }
    
    public function getTotalJobsCreatedSum($projects)
    {
        return $projects->sum(function ($project) {
            return $project->reports->sum('ft_total') + $project->reports->sum('pt_total');
        });
    }

    public function getTotalTreesRestoredSum($projects)
    {
        return $projects->sum(function ($project) {
            return $project->trees_planted_count;
        });
    }

    public function getTotalTreesGrownGoalSum($projects)
    {
        return $projects->sum('trees_grown_goal');
    }

    public function getTotalHectaresSum($projects)
    {
        return $projects->sum(function ($project) {
            return $project->sitePolygons->sum('calc_area');
        });
    }
}

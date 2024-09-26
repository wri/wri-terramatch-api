<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TotalTerrafundHeaderDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();
        $countryName = '';
        if (data_get($request, 'filter.country')) {
            $countryName = WorldCountryGeneralized::where('iso', $request['filter']['country'])->first()->country;
        }
        $response = (object)[
            'total_non_profit_count' => $this->getTotalNonProfitCount($projects),
            'total_enterprise_count' => $this->getTotalEnterpriseCount($projects),
            'total_entries' => $this->getTotalJobsCreatedSum($projects),
            'total_hectares_restored' => round($this->getTotalHectaresSum($projects)),
            'total_hectares_restored_goal' => $this->getTotalHectaresRestoredGoalSum($projects),
            'total_trees_restored' => $this->getTotalTreesRestoredSum($projects),
            'total_trees_restored_goal' => $this->getTotalTreesGrownGoalSum($projects),
            'country_name' => $countryName,
        ];

        return response()->json($response);
    }

    public function getTotalNonProfitCount($projects)
    {
        $projects = $projects->filter(function ($project) {
            return $project->organisation->type === 'non-profit-organization';
        });

        return $projects->count();
    }

    public function getTotalEnterpriseCount($projects)
    {
        $projects = $projects->filter(function ($project) {
            return $project->organisation->type === 'for-profit-organization';
        });

        return $projects->count();
    }

    public function getTotalJobsCreatedSum($projects)
    {
        return $projects->sum(function ($project) {
            $totalSum = $project->reports()->selectRaw('SUM(ft_total) as total_ft, SUM(pt_total) as total_pt')->first();

            return $totalSum->total_ft + $totalSum->total_pt;
        });
    }

    public function getTotalHectaresRestoredGoalSum($projects)
    {
        return $projects->sum('total_hectares_restored_goal');
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

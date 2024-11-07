<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Models\V2\WorldCountryGeneralized;
use Illuminate\Http\Request;

class RunTotalHeaderService
{
    public function runTotalHeaderJob(Request $request)
    {
        $projects = $this->getProjectsData($request);
        $countryName = $this->getCountryName($request);

        $response = (object) [
            'total_non_profit_count' => $projects->where('organisation.type', 'non-profit-organization')->count(),
            'total_enterprise_count' => $projects->where('organisation.type', 'for-profit-organization')->count(),
            'total_entries' => $projects->sum('total_jobs_created'),
            'total_hectares_restored' => round($projects->sum('total_hectares_restored')),
            'total_hectares_restored_goal' => $projects->sum('total_hectares_restored_goal'),
            'total_trees_restored' => $projects->sum('trees_planted_count'),
            'total_trees_restored_goal' => $projects->sum('trees_grown_goal'),
            'country_name' => $countryName,
        ];

        return $response;
    }

    private function getProjectsData(Request $request)
    {
        return TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->with(['organisation:id,type,name'])
            ->select([
                'v2_projects.id',
                'v2_projects.organisation_id',
                'v2_projects.total_hectares_restored_goal',
                'v2_projects.trees_grown_goal',
            ])
            ->get()
            ->map(function ($project) {
                $project->total_hectares_restored = $project->sitePolygons->sum('calc_area');

                return $project;
            });
    }

    private function getCountryName(Request $request)
    {
        $country = data_get($request, 'filter.country');
        if ($country) {
            return WorldCountryGeneralized::where('iso', $country)
                ->select('country')
                ->first()
                ->country;
        }

        return '';
    }
}

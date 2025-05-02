<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use Illuminate\Http\Request;
use App\Models\V2\WorldCountryGeneralized;

class RunActiveCountriesTableService
{
    private function getProjects(Request $request)
    {
        return TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();
    }

    public function getAllCountries(Request $request)
    {
        $projects = $this->getProjects($request);
        $countries = WorldCountryGeneralized::orderBy('country')
            ->get(['country', 'iso']);

        $activeCountries = [];

        foreach ($countries as $country) {
            $countryProjects = $projects->where('country', $country->iso);
            if ($countryProjects->isEmpty()) {
                continue;
            }

            $activeCountries[] = [
                'country_slug' => $country->iso,
                'country' => $country->country,
                'number_of_projects' => $countryProjects->count(),
                'total_trees_planted' => $this->sumField($countryProjects, 'approved_trees_planted_count'),
                'total_jobs_created' => $this->sumField($countryProjects, 'total_approved_jobs_created'),
                'hectares_restored' => round($this->sumHectares($countryProjects)),
            ];
        }

        return $activeCountries;
    }

    private function sumField($projects, $field)
    {
        return $projects->sum(function ($project) use ($field) {
            return $project->$field ?? 0;
        });
    }

    private function sumHectares($projects)
    {
        return $projects->sum('total_hectares_restored_sum');
    }
}

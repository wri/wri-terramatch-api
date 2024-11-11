<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Http\Request;

class RunActiveCountriesTableService
{
    private function getProjects(Request $request)
    {
        return TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();
    }

    public function getAllCountries(Request $request)
    {
        $projects = $this->getProjects($request);
        $countryId = FormOptionList::where('key', 'countries')->value('id');
        $countries = FormOptionListOption::where('form_option_list_id', $countryId)
            ->orderBy('label')
            ->get(['slug', 'label']);

        $activeCountries = [];

        foreach ($countries as $country) {
            $countryProjects = $projects->where('country', $country->slug);
            if ($countryProjects->isEmpty()) {
                continue;
            }

            $activeCountries[] = [
                'country_slug' => $country->slug,
                'country' => $country->label,
                'number_of_projects' => $countryProjects->count(),
                'total_trees_planted' => $this->sumField($countryProjects, 'trees_planted_count'),
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

<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\ActiveCountriesTableResource;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;

class ActiveCountriesTableController extends Controller
{
    public function __invoke(): ActiveCountriesTableResource
    {
        $response = (object) [
            'data' => $this->getAllCountries(),
        ];

        return new ActiveCountriesTableResource($response);
    }

    public function getAllCountries()
    {
        $projects = Project::where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) {
                $query->whereIn('type', ['for-profit-organization', 'non-profit-organization']);
            })->get();
        $countryId = FormOptionList::where('key', 'countries')->value('id');
        $countries = FormOptionListOption::where('form_option_list_id', $countryId)
            ->orderBy('label')
            ->get(['slug', 'label']);
        $activeCountries = [];
        foreach ($countries as $country) {
            $totalProjects = $this->numberOfProjects($country->slug, $projects);
            if ($totalProjects <= 0) {
                continue;
            }

            $totalSpeciesAmount = $this->totalSpeciesAmount($country->slug, $projects);

            $totalJobsCreated = $this->totalJobsCreated($country->slug, $projects);

            $numberOfSites = $this->numberOfSites($country->slug, $projects);

            $totalNurseries = $this->numberOfNurseries($country->slug, $projects);

            $activeCountries[] = [
                'country_slug' => $country->slug,
                'country' => $country->label,
                'number_of_projects' => $totalProjects,
                'total_trees_planted' => $totalSpeciesAmount,
                'total_jobs_created' => $totalJobsCreated,
                'number_of_sites' => $numberOfSites,
                'number_of_nurseries' => $totalNurseries,
            ];
        }

        return $activeCountries;
    }

    public function numberOfProjects($country, $projects)
    {
        return $projects->where('country', $country)->count();
    }

    public function totalSpeciesAmount($country, $projects)
    {
        $projects = $projects->where('country', $country);

        return $projects->sum(function ($project) {
            return $project->sites()->with(['reports.treeSpecies'])->get()->sum(function ($site) {
                return $site->reports->sum(function ($report) {
                    return $report->treeSpecies->sum('amount');
                });
            });
        });
    }

    public function totalJobsCreated($country, $projects)
    {
        $projects = $projects->where('country', $country);

        return $projects->sum(function ($project) {
            $totalSum = $project->reports()
                ->groupBy('project_id')
                ->selectRaw('SUM(ft_total) as total_ft, SUM(pt_total) as total_pt')->first();

            if ($totalSum) {
                return $totalSum->total_ft + $totalSum->total_pt;
            } else {
                return 0;
            }
        });
    }

    public function numberOfSites($country, $projects)
    {
        $projectIds = $projects->where('country', $country)->pluck('id');

        return Site::whereIn('project_id', $projectIds)->count();
    }

    public function numberOfNurseries($country, $projects)
    {
        $projectIds = $projects->where('country', $country)->pluck('id');

        return Nursery::whereIn('project_id', $projectIds)->count();
    }
}

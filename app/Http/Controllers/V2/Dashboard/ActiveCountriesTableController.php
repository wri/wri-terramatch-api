<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Projects\Project;
use Illuminate\Support\Facades\DB;

class ActiveCountriesTableController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'active_countries' => $this->getAllCountries(),
        ]);
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
            ->get();
        $activeCountries = [];
        foreach ($countries as $country) {
            $totalProjects = $this->numberOfProjects($country->slug, $projects);

            $totalSpeciesAmount = $this->totalSpeciesAmount($country->slug, $projects);

            $totalJobsCreated = $this->totalJobsCreated($country->slug, $projects);

            $numberOfSites = $this->numberOfSites($country->slug, $projects);

            $totalNurseries = $this->numberOfNurseries($country->slug, $projects);

            $activeCountries[] = [
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
            return $project->sites()->get()->sum(function ($site) {
                $latestReport = $site->reports()->orderByDesc('due_at')->first();
                if ($latestReport) {
                    return $latestReport->treeSpecies()->sum('amount');
                }
            });
        });
    }

    public function totalJobsCreated($country, $projects)
    {
        $projects = $projects->where('country', $country);

        return $projects->sum(function ($project) {
            $latestProjectReport = $project->reports()
                ->orderByDesc('due_at')
                ->value(DB::raw('pt_total + ft_total'));

            return $latestProjectReport;
        });
    }

    public function numberOfSites($country, $projects)
    {
        $projects = $projects->where('country', $country);

        return $projects->sum(function ($project) {
            return $project->sites()->count();
        });
    }

    public function numberOfNurseries($country, $projects)
    {
        $projects = $projects->where('country', $country);

        return $projects->sum(function ($project) {
            return $project->nurseries()->count();
        });
    }
}

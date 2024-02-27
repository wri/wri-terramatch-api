<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
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
        $countryId = FormOptionList::where('key', 'countries')->value('id');
        $countries = FormOptionListOption::where('form_option_list_id', $countryId)
            ->orderBy('label')
            ->get();
        $activeCountries = [];
        foreach ($countries as $country) {
            $totalProjects = $this->numberOfProjects($country->slug);

            $totalSpeciesAmount = $this->totalSpeciesAmount($country->slug);

            $totalJobsCreated = $this->totalJobsCreated($country->slug);

            $numberOfSites = $this->numberOfSites($country->slug);

            $totalNurseries = $this->numberOfNurseries($country->slug);

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

    public function projectsCountry($country)
    {
        return Project::where('framework_key', 'terrafund')
            ->where('country', $country);
    }

    public function numberOfProjects($country)
    {
        return $this->projectsCountry($country)->count();
    }

    public function totalSpeciesAmount($country)
    {
        $projects = $this->projectsCountry($country)->get();
        $totalSpeciesAmount = 0;
        foreach ($projects as $project) {
            $sites = $project->sites()->pluck('id')->toArray();
            foreach ($sites as $siteId) {
                $latestSiteReportId = SiteReport::where('site_id', $siteId)
                    ->orderByDesc('due_at')
                    ->value('id');
                if ($latestSiteReportId !== null) {
                    $totalSpeciesAmount += TreeSpecies::where('speciesable_id', $latestSiteReportId)->sum('amount');
                }
            }
        }

        return $totalSpeciesAmount;
    }

    public function totalJobsCreated($country)
    {
        $projects = $this->projectsCountry($country)->get();
        $totalJobs = 0;
        foreach ($projects as $project) {
            $latestProjectReport = ProjectReport::where('project_id', $project->id)
                ->orderByDesc('due_at')
                ->value(DB::raw('pt_total + ft_total'));
            $totalJobs += $latestProjectReport;
        }

        return $totalJobs;
    }

    public function numberOfSites($country)
    {
        $projects = $this->projectsCountry($country)->get();

        return $projects->sum(function ($project) {
            return $project->sites()->count();
        });
    }

    public function numberOfNurseries($country)
    {
        $projects = $this->projectsCountry($country)->get();

        return $projects->sum(function ($project) {
            return $project->nurseries()->count();
        });
    }
}

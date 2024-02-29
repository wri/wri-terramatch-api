<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActiveProjectsTableController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'project_list' => $this->getAllProjects($request),
        ]);
    }

    public function getAllProjects($request)
    {
        $projects = Project::query();
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($projects, $request)
            ->pluck('id')->toArray();
        $activeProjects = [];
        foreach ($projects as $projectId) {
            $name = $this->projectName($projectId);
            $organisation = $this->projectOrganisation($projectId);
            $treesUnderRestoration = $this->treesUnderRestoration($projectId);
            $jobsCreated = $this->jobsCreated($projectId);
            $volunteers = $this->volunteers($projectId);
            $beneficiaries = $this->beneficiaries($projectId);
            $survivalRate = $this->survivalRate($projectId);
            $numberOfSites = $this->numberOfSites($projectId);
            $numberOfNurseries = $this->numberOfNurseries($projectId);
            $country = $this->projectCountry($projectId);
            $numberOfTreesGoal = $this->numberOfTreesGoal($projectId);
            $dateAdded = $this->projectDateAdded($projectId);
            $activeProjects[] = [
                'name' => $name,
                'organisation' => $organisation,
                'trees_under_restoration' => $treesUnderRestoration,
                'jobs_created' => $jobsCreated,
                'volunteers' => $volunteers,
                'beneficiaries' => $beneficiaries,
                'survival_rate' => $survivalRate,
                'number_of_sites' => $numberOfSites,
                'number_of_nurseries' => $numberOfNurseries,
                'project_country' => $country,
                'number_of_trees_goal' => $numberOfTreesGoal,
                'date_added' => $dateAdded,
            ];
        }

        return $activeProjects;
    }

    public function projectName($projectId)
    {
        return Project::where('id', $projectId)->value('name');
    }

    public function projectOrganisation($projectId)
    {
        return Project::where('id', $projectId)->first()->organisation->name;
    }

    public function treesUnderRestoration($projectId)
    {
        $project = Project::where('id', $projectId)->first();
        $sites = $project->sites()->get();

        return $sites->sum(function ($site) {
            $latestSiteReportId = SiteReport::where('site_id', $site->id)
                ->orderByDesc('due_at')
                ->value('id');

            return TreeSpecies::where('speciesable_id', $latestSiteReportId)->sum('amount');
        });
    }

    public function jobsCreated($projectId)
    {
        $project = Project::where('id', $projectId)->get();

        return $project->sum(function ($project) {
            return ProjectReport::where('project_id', $project->id)
                ->orderByDesc('due_at')
                ->value(DB::raw('pt_total + ft_total'));
        });
    }

    public function volunteers($projectId)
    {
        return ProjectReport::where('project_id', $projectId)
            ->orderByDesc('due_at')
            ->value('volunteer_total');
    }

    public function beneficiaries($projectId)
    {
        return ProjectReport::where('project_id', $projectId)
            ->orderByDesc('due_at')
            ->value('beneficiaries');
    }

    public function survivalRate($projectId)
    {
        return Project::where('id', $projectId)->value('survival_rate');
    }

    public function numberOfSites($projectId)
    {
        $project = Project::where('id', $projectId)->first();

        return $project->sites()->count();
    }

    public function numberOfNurseries($projectId)
    {
        $project = Project::where('id', $projectId)->first();

        return $project->nurseries()->count();
    }

    public function projectCountry($projectId)
    {
        $countryId = FormOptionList::where('key', 'countries')->value('id');
        $projectCountrySlug = Project::where('id', $projectId)->value('country');

        return FormOptionListOption::where('form_option_list_id', $countryId)
            ->where('slug', $projectCountrySlug)
            ->value('label');
    }

    public function numberOfTreesGoal($projectId)
    {
        return Project::where('id', $projectId)->value('trees_grown_goal');
    }

    public function projectDateAdded($projectId)
    {
        return Project::where('id', $projectId)->value('created_at');
    }
}

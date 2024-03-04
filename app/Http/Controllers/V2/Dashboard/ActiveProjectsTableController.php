<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
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
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();
        $activeProjects = [];
        foreach ($projects as $project) {
            $activeProjects[] = [
                'name' => $project->name,
                'organisation' => $project->organisation->name,
                'trees_under_restoration' => $this->treesUnderRestoration($project),
                'jobs_created' => $this->jobsCreated($project),
                'volunteers' => $this->volunteers($project),
                'beneficiaries' => $this->beneficiaries($project),
                'survival_rate' => $project->survival_rate,
                'number_of_sites' => $project->sites()->count(),
                'number_of_nurseries' => $project->nurseries()->count(),
                'project_country' => $this->projectCountry($project->country),
                'number_of_trees_goal' => $project->trees_grown_goal,
                'date_added' => $project->created_at,
            ];
        }

        return $activeProjects;
    }

    public function treesUnderRestoration($project)
    {
        return $project->sites->sum(function ($site) {
            $latestReport = $site->reports()->orderByDesc('due_at')->first();
            if ($latestReport) {
                return $latestReport->treeSpecies()->sum('amount');
            } else {
                return 0;
            }
        });
    }

    public function jobsCreated($project)
    {
        return $project->reports()->orderByDesc('due_at')->value(DB::raw('pt_total + ft_total'));
    }

    public function volunteers($project)
    {
        return $project->reports()->orderByDesc('due_at')->value('volunteer_total');
    }

    public function beneficiaries($project)
    {
        return $project->reports()->orderByDesc('due_at')->value('beneficiaries');
    }

    public function projectCountry($slug)
    {
        $countryId = FormOptionList::where('key', 'countries')->value('id');

        return FormOptionListOption::where('form_option_list_id', $countryId)
            ->where('slug', $slug)
            ->value('label');
    }
}

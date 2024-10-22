<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActiveProjectsTableController extends Controller
{
    public function __invoke(Request $request)
    {
        $perPage = $request->input('per_page', PHP_INT_MAX);
        $page = $request->input('page', 1);

        $projects = $this->getAllProjects($request, $perPage, $page);
        $count = $this->getQuery($request)->count();

        return response()->json([
            'current_page' => $page,
            'data' => $projects,
            'per_page' => $perPage,
            'last_page' => ceil($count / $perPage),
            'total' => $count,
        ]);
    }

    public function getQuery($request)
    {
        return TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->with('organisation')
            ->withCount(['sites', 'nurseries']);
    }

    public function getAllProjects($request, $perPage, $page)
    {
        $query = $this->getQuery($request)
            ->skip(($page - 1) * $perPage)
            ->take($perPage);

        $projects = $query->get();

        return $projects->map(function ($project) {
            return [
                'uuid' => $project->uuid,
                'name' => $project->name,
                'organisation' => $project->organisation->name,
                'trees_under_restoration' => $this->treesUnderRestoration($project),
                'jobs_created' => $this->jobsCreated($project),
                'volunteers' => $this->volunteers($project),
                'beneficiaries' => $this->beneficiaries($project),
                'survival_rate' => $project->survival_rate,
                'number_of_sites' => $project->sites_count,
                'number_of_nurseries' => $project->nurseries_count,
                'project_country' => $this->projectCountry($project->country),
                'country_slug' => $project->country,
                'number_of_trees_goal' => $project->trees_grown_goal,
                'date_added' => $project->created_at,
                'hectares_under_restoration' => round($project->sitePolygons->sum('calc_area')),
                'programme' => $project->framework_key,
            ];
        });
    }

    public function treesUnderRestoration($project)
    {
        return $project->trees_planted_count;
    }

    public function jobsCreated($project)
    {
        $projectReport = $project->reports()
            ->selectRaw('SUM(ft_total) as total_ft, SUM(pt_total) as total_pt')
            ->groupBy('project_id')
            ->first();

        if ($projectReport) {
            return $projectReport->total_ft + $projectReport->total_pt;
        } else {
            return 0;
        }
    }

    public function volunteers($project)
    {
        $totalVolunteers = $project->reports()->selectRaw('SUM(volunteer_total) as total')->first();

        return $totalVolunteers ? intval($totalVolunteers->total) : 0;
    }

    public function beneficiaries($project)
    {
        $totalBeneficiaries = $project->reports()->selectRaw('SUM(beneficiaries) as total')->first();

        return $totalBeneficiaries ? intval($totalBeneficiaries->total) : 0;
    }

    public function projectCountry($slug)
    {
        $countryId = FormOptionList::where('key', 'countries')->value('id');

        return FormOptionListOption::where('form_option_list_id', $countryId)
            ->where('slug', $slug)
            ->value('label');
    }

    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (LengthAwarePaginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}

<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RunActiveProjectsService
{
    public function runActiveProjectsJob(Request $request)
    {
        $perPage = $request->input('per_page', PHP_INT_MAX);
        $page = $request->input('page', 1);

        $projects = $this->getAllProjects($request, $perPage, $page);
        $count = $this->getQuery($request)->count();

        return (object) [
            'current_page' => $page,
            'data' => $projects,
            'per_page' => $perPage,
            'last_page' => ceil($count / $perPage),
            'total' => $count,
        ];
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
                'trees_under_restoration' => $project->approved_trees_planted_count,
                'jobs_created' => $project->total_approved_jobs_created ?? 0,
                'volunteers' => $project->approved_volunteers_count ?? 0,
                'project_country' => $this->projectCountry($project->country),
                'country_slug' => $project->country,
                'hectares_under_restoration' => $project->total_hectares_restored_sum,
                'programme' => $project->framework_key,
            ];
        });
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

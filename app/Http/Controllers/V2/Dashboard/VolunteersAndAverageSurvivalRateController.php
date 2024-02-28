<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;

class VolunteersAndAverageSurvivalRateController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'total_volunteers' => $this->getTotalVolunteerSum($request),
            'men_volunteers' => $this->getVolunteersSum($request, 'volunteer_men'),
            'women_volunteers' => $this->getVolunteersSum($request, 'volunteer_women'),
            'youth_volunteers' => $this->getVolunteersSum($request, 'volunteer_youth'),
            'non_youth_volunteers' => $this->getVolunteersSum($request, 'volunteer_non_youth'),
            'non_profit_survival_rate' => $this->getAverageSurvivalRate($request, 'non-profit-organization'),
            'enterprise_survival_rate' => $this->getAverageSurvivalRate($request, 'for-profit-organization'),
        ]);
    }

    public function getTotalVolunteerSum($request)
    {
        $query = Project::query();
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request)->get();

        return $projects->sum(function ($project) {
            return ProjectReport::where('project_id', $project->id)
                ->orderByDesc('due_at')
                ->value('volunteer_total');
        });
    }

    public function getVolunteersSum($request, $volunteerType)
    {
        $query = Project::query();
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request)->get();

        return $projects->sum(function ($project) use ($volunteerType) {
            return ProjectReport::where('project_id', $project->id)->sum($volunteerType);
        });
    }

    public function getAverageSurvivalRate($request, $typeOrganisation)
    {
        $query = Project::query();
        $query = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);
        $query = $query->whereHas('organisation', function ($query) use ($typeOrganisation) {
            $query->where('type', $typeOrganisation);
        });
        $average = $query->avg('survival_rate');

        return intval($average);
    }
}

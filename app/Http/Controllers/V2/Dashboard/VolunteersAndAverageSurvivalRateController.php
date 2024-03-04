<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VolunteersAndAverageSurvivalRateController extends Controller
{
    public function __invoke(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();

        return response()->json([
            'total_volunteers' => $this->getTotalVolunteerSum($projects),
            'men_volunteers' => $this->getVolunteersSum($projects, 'volunteer_men'),
            'women_volunteers' => $this->getVolunteersSum($projects, 'volunteer_women'),
            'youth_volunteers' => $this->getVolunteersSum($projects, 'volunteer_youth'),
            'non_youth_volunteers' => $this->getVolunteersSum($projects, 'volunteer_non_youth'),
            'non_profit_survival_rate' => $this->getAverageSurvivalRate($projects, 'non-profit-organization'),
            'enterprise_survival_rate' => $this->getAverageSurvivalRate($projects, 'for-profit-organization'),
        ]);
    }

    public function getTotalVolunteerSum($projects)
    {
        return $projects->sum(function ($project) {
            return $project->reports()->orderByDesc('due_at')->value('volunteer_total');
        });
    }

    public function getVolunteersSum($projects, $volunteerType)
    {
        return $projects->sum(function ($project) use ($volunteerType) {
            return $project->reports()->sum($volunteerType);
        });
    }

    public function getAverageSurvivalRate($projects, $typeOrganisation)
    {
        $projects = $projects->filter(function ($project) use ($typeOrganisation) {
            return $project->organisation->type === $typeOrganisation;
        });
        $average = $projects->avg('survival_rate');

        return intval($average);
    }
}

<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\VolunteersAndAverageResource;
use Illuminate\Http\Request;

class VolunteersAndAverageSurvivalRateController extends Controller
{
    public function __invoke(Request $request): VolunteersAndAverageResource
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();

        $response = (object)[
            'total_volunteers' => $this->getTotalVolunteerSum($projects),
            'men_volunteers' => $this->getVolunteersSum($projects, 'volunteer_men'),
            'women_volunteers' => $this->getVolunteersSum($projects, 'volunteer_women'),
            'youth_volunteers' => $this->getVolunteersSum($projects, 'volunteer_youth'),
            'non_youth_volunteers' => $this->getVolunteersSum($projects, 'volunteer_non_youth'),
            'non_profit_survival_rate' => $this->getAverageSurvivalRate($projects, 'non-profit-organization'),
            'enterprise_survival_rate' => $this->getAverageSurvivalRate($projects, 'for-profit-organization'),
            'number_of_sites' => $this->numberOfSites($projects),
            'number_of_nurseries' => $this->numberOfNurseries($projects)
        ];

        return new VolunteersAndAverageResource($response);
    }

    public function getTotalVolunteerSum($projects)
    {
        return $projects->sum(function ($project) {
            return $project->reports()->sum('volunteer_total');
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
        })->flatMap(function ($project) {
            return $project->reports;
        })->avg('pct_survival_to_date');

        return intval($average);
    }

    public function numberOfSites($projects)
    {
        return $projects->sum(function ($project) {
            return $project->sites->count();
        });
    }

    public function numberOfNurseries($projects)
    {
        return $projects->sum(function ($project) {
            return $project->nurseries->count();
        });
    }
}

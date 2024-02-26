<?php

namespace App\Http\Controllers\V2\Dashboard;

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

    public function buildQueryFromRequest($query, $request)
    {
        if ($request->has('country')) {
            $country = $request->input('country');
            $query->where('country', $country);
        } elseif ($request->has('uuid')) {
            $projectId = $request->input('uuid');
            $query->where('uuid', $projectId);
        }
        return $query;
    }

    public function getTotalVolunteerSum($request)
    {
        $query = Project::where('framework_key', 'terrafund');
        $query = $this->buildQueryFromRequest($query, $request);
        $projectsId = $query->pluck('id')->toArray();
        $totalVolunteers = 0;
        foreach ($projectsId as $id) {
            $totalVolunteer = ProjectReport::where('project_id', $id)
                ->orderByDesc('due_at')
                ->value('volunteer_total');

            $totalVolunteers += $totalVolunteer;
        }

        return $totalVolunteers;
    }

    public function getVolunteersSum($request, $volunteerType)
    {
        $query = Project::where('framework_key', 'terrafund');
        $query = $this->buildQueryFromRequest($query, $request);
        $projectsId = $query->pluck('id')->toArray();
        $Volunteers = 0;
        foreach ($projectsId as $id) {
            $valueMenVolunteer = ProjectReport::where('project_id', $id)
                ->sum($volunteerType);

            $Volunteers += $valueMenVolunteer;
        }

        return $Volunteers;
    }

    public function getAverageSurvivalRate($request, $typeOrganisation)
    {
        $query = Project::where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) use ($typeOrganisation) {
                $query->where('type', $typeOrganisation);
            });
        $query = $this->buildQueryFromRequest($query, $request);
        $average = $query->avg('survival_rate');

        return intval($average);
    }
}

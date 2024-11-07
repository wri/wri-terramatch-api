<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class RunVolunteersAverageService
{
    public function runVolunteersAverageJob(Request $request): object
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->with(['reports', 'organisation', 'sites', 'nurseries'])
            ->get();

        return (object) [
            'total_volunteers' => $this->getTotalVolunteerSum($projects),
            'men_volunteers' => $this->getVolunteersSum($projects, 'volunteer_men'),
            'women_volunteers' => $this->getVolunteersSum($projects, 'volunteer_women'),
            'youth_volunteers' => $this->getVolunteersSum($projects, 'volunteer_youth'),
            'non_youth_volunteers' => $this->getVolunteersSum($projects, 'volunteer_non_youth'),
            'number_of_sites' => $this->numberOfSites($projects)
        ];
    }

    /**
     * Get total volunteer count by summing up volunteer totals from all reports.
     *
     * @param Collection $projects
     * @return int
     */
    public function getTotalVolunteerSum(Collection $projects): int
    {
        return $projects->sum(function ($project) {
            return $project->reports->sum('volunteer_total');
        });
    }

    /**
     * Get sum of a specific type of volunteer (e.g., men, women, youth).
     *
     * @param Collection $projects
     * @param string $volunteerType
     * @return int
     */
    public function getVolunteersSum(Collection $projects, string $volunteerType): int
    {
        return $projects->sum(function ($project) use ($volunteerType) {
            return $project->reports->sum($volunteerType);
        });
    }

    /**
     * Get total number of sites across all projects.
     *
     * @param Collection $projects
     * @return int
     */
    public function numberOfSites(Collection $projects): int
    {
        return $projects->sum(fn($project) => $project->sites->count());
    }
}

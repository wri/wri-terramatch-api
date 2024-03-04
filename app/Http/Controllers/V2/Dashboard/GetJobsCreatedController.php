<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;
use App\Http\Resources\V2\Dashboard\JobsCreatedResource;

class GetJobsCreatedController extends Controller
{
    public function __invoke(Request $request): JobsCreatedResource
    {
        $organizationTypes = ['non-profit-organization', 'for-profit-organization'];

        $query = Project::join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->where('v2_projects.framework_key', '=', 'terrafund')
            ->whereIn('organisations.type', $organizationTypes);

        if ($request->has('country')) {
            $country = $request->input('country');
            $query->where('country', $country);
        } elseif ($request->has('uuid')) {
            $projectUuid = $request->input('uuid');
            $query->where('v2_projects.uuid', $projectUuid);
        }

        $rawProjectIds = $query->select('v2_projects.id', 'organisations.type')->get();

        $forProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'for-profit-organization');
        $nonProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'non-profit-organization');
        $allProjectIds = $this->getAllProjectIds($rawProjectIds);

        $forProfitJobsCreated = $this->getTotalJobsCreated($forProfitProjectIds);
        $nonProfitJobsCreated = $this->getTotalJobsCreated($nonProfitProjectIds);
        $totalJobsCreated = $this->getTotalJobsCreated($allProjectIds);
        $jobsCreatedDetailed = $this->getJobsCreatedDetailed($allProjectIds);

        $finalResult = (object) [
            'totalJobsCreated' => $totalJobsCreated,
            'forProfitJobsCreated' => $forProfitJobsCreated,
            'nonProfitJobsCreated' => $nonProfitJobsCreated,
            'total_ft' => $jobsCreatedDetailed->total_ft,
            'total_pt' => $jobsCreatedDetailed->total_pt,
            'total_men' => $this->calculateTotalMen($jobsCreatedDetailed),
            'total_pt_men' => $jobsCreatedDetailed->total_pt_men,
            'total_ft_men' => $jobsCreatedDetailed->total_ft_men,
            'total_women' => $this->calculateTotalWomen($jobsCreatedDetailed),
            'total_pt_women' => $jobsCreatedDetailed->total_pt_women,
            'total_ft_women' => $jobsCreatedDetailed->total_ft_women,
            'total_youth' => $this->calculateTotalYouth($jobsCreatedDetailed),
            'total_pt_youth' => $jobsCreatedDetailed->total_pt_youth,
            'total_ft_youth' => $jobsCreatedDetailed->total_ft_youth,
            'total_non_youth' => $this->calculateTotalNonYouth($jobsCreatedDetailed),
            'total_pt_non_youth' => $jobsCreatedDetailed->total_pt_non_youth,
            'total_ft_non_youth' => $jobsCreatedDetailed->total_ft_non_youth,
        ];

        return new JobsCreatedResource($finalResult);
    }

    private function filterProjectIdsByType($projectIds, $type)
    {
        return $projectIds->filter(function ($row) use ($type) {
            return $row->type === $type;
        })->pluck('id')->toArray();
    }

    private function getAllProjectIds($projectIds)
    {
        return $projectIds->pluck('id')->toArray();
    }

    private function calculateTotalMen($jobsCreatedDetailed)
    {
        return $jobsCreatedDetailed->total_pt_men + $jobsCreatedDetailed->total_ft_men;
    }

    private function calculateTotalWomen($jobsCreatedDetailed)
    {
        return $jobsCreatedDetailed->total_pt_women + $jobsCreatedDetailed->total_ft_women;
    }

    private function calculateTotalYouth($jobsCreatedDetailed)
    {
        return $jobsCreatedDetailed->total_pt_youth + $jobsCreatedDetailed->total_ft_youth;
    }

    private function calculateTotalNonYouth($jobsCreatedDetailed)
    {
        return $jobsCreatedDetailed->total_pt_non_youth + $jobsCreatedDetailed->total_ft_non_youth;
    }

    private function getTotalJobsCreated($projectIds)
    {
        $sumData = ProjectReport::whereIn('project_id', $projectIds)
            ->where('framework_key', 'terrafund')
            ->selectRaw('SUM(ft_total) as total_ft, SUM(pt_total) as total_pt')
            ->first();

        return $sumData->total_ft + $sumData->total_pt;
    }

    private function getJobsCreatedDetailed($projectIds)
    {
        return ProjectReport::whereIn('project_id', $projectIds)
            ->where('framework_key', 'terrafund')
            ->selectRaw(
                'SUM(ft_total) as total_ft, 
                 SUM(pt_total) as total_pt, 
                 SUM(pt_men) as total_pt_men, 
                 SUM(ft_men) as total_ft_men, 
                 SUM(pt_women) as total_pt_women, 
                 SUM(ft_women) as total_ft_women, 
                 SUM(pt_youth) as total_pt_youth, 
                 SUM(ft_youth) as total_ft_youth, 
                 SUM(pt_non_youth) as total_pt_non_youth, 
                 SUM(ft_jobs_non_youth) as total_ft_non_youth'
            )
            ->first();
    }
}

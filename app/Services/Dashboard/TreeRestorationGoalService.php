<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\StateMachines\ReportStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TreeRestorationGoalService
{
    public function calculateTreeRestorationGoal(Request $request)
    {
        $query = TerrafundDashboardQueryHelper::buildQueryFromRequest($request);
        $rawProjectIds = $this->getRawProjectIds($query);
        $allProjectIds = $this->getProjectIdsFromCollection($rawProjectIds);
        $siteIds = $this->getSiteIds($allProjectIds);
        $distinctDates = $this->getDistinctDates($siteIds);

        $forProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'for-profit-organization');
        $nonProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'non-profit-organization');
        $forProfitSiteIds = $this->getSiteIds($forProfitProjectIds);
        $nonProfitSiteIds = $this->getSiteIds($nonProfitProjectIds);

        $forProfitTreeCount = $this->treeCountByDueDate($forProfitProjectIds);
        $nonProfitTreeCount = $this->treeCountByDueDate($nonProfitProjectIds);
        $totalTreesGrownGoal = $query->sum('trees_grown_goal');

        return [
            'forProfitTreeCount' => (int) $forProfitTreeCount,
            'nonProfitTreeCount' => (int) $nonProfitTreeCount,
            'totalTreesGrownGoal' => (int) $totalTreesGrownGoal,
            'treesUnderRestorationActualTotal' => $this->treeCountPerPeriod($siteIds, $distinctDates, $totalTreesGrownGoal),
            'treesUnderRestorationActualForProfit' => $this->treeCountPerPeriod($forProfitSiteIds, $distinctDates, $totalTreesGrownGoal),
            'treesUnderRestorationActualNonProfit' => $this->treeCountPerPeriod($nonProfitSiteIds, $distinctDates, $totalTreesGrownGoal),
        ];
    }

    private function getRawProjectIds($query)
    {
        return $query->select('v2_projects.id', 'organisations.type')->get();
    }

    private function getProjectIdsFromCollection($projectIds)
    {
        return $projectIds->pluck('id')->toArray();
    }

    private function getSiteIds($projectIds)
    {
        return Site::whereIn('project_id', $projectIds)
            ->whereIn('status', Site::$approvedStatuses)
            ->pluck('id');
    }

    private function getDistinctDates($siteIds)
    {
        return SiteReport::selectRaw('YEAR(due_at) as year, MONTH(due_at) as month')
            ->where('status', ReportStatusStateMachine::APPROVED)
            ->whereNotNull('due_at')
            ->whereIn('site_id', $siteIds)
            ->groupBy('year', 'month')
            ->get()
            ->toArray();
    }

    private function filterProjectIdsByType($projectIds, $type)
    {
        return collect($projectIds)->filter(fn ($row) => $row->type === $type)->pluck('id')->toArray();
    }

    private function treeCountByDueDate(array $projectIds)
    {
        $projects = Project::whereIn('id', $projectIds)->get();

        return $projects->sum('approved_trees_planted_count');
    }

    private function treeCountPerPeriod($siteIds, $distinctDates, $totalTreesGrownGoal)
    {
        $treesUnderRestorationActual = collect();

        foreach ($distinctDates as $date) {
            $year = $date['year'];
            $month = $date['month'];

            $treeSpeciesAmount = $this->calculateTreeSpeciesAmountForPeriod($siteIds, $year, $month);

            $formattedDate = Carbon::create($year, $month, 1);

            $treesUnderRestorationActual->push([
                'dueDate' => $formattedDate,
                'treeSpeciesAmount' => (int)$treeSpeciesAmount,
                'treeSpeciesPercentage' => $this->calculatePercentage($treeSpeciesAmount, $totalTreesGrownGoal),
            ]);
        }

        return $treesUnderRestorationActual->toArray();
    }

    private function calculateTreeSpeciesAmountForPeriod($siteIds, $year, $month)
    {
        return SiteReport::whereIn('site_id', $siteIds)
            ->where('v2_site_reports.status', ReportStatusStateMachine::APPROVED)
            ->whereYear('v2_site_reports.due_at', $year)
            ->whereMonth('v2_site_reports.due_at', $month)
            ->get()
            ->sum(function ($report) {
                return $report->treeSpecies()->where('collection', TreeSpecies::COLLECTION_PLANTED)->sum('amount');
            });
    }

    private function calculatePercentage($treeSpeciesAmount, $totalTreesGrownGoal)
    {
        if ($totalTreesGrownGoal == 0) {
            return 0;
        }

        return round(($treeSpeciesAmount / $totalTreesGrownGoal) * 100, 3);
    }
}

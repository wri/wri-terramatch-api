<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\ViewTreeRestorationGoalResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\StateMachines\EntityStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewTreeRestorationGoalController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->prepareProjectQuery($request);
        $rawProjectIds = $this->getRawProjectIds($query);
        $allProjectIds = $this->getAllProjectIds($rawProjectIds);
        $siteIds = $this->getSiteIds($allProjectIds);
        $distinctDates = $this->getDistinctDates($siteIds);
        $latestDueDate = $this->getLatestDueDate($distinctDates);

        $forProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'for-profit-organization');
        $nonProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'non-profit-organization');
        $forProfitSiteIds = $this->getSiteIds($forProfitProjectIds);
        $nonProfitSiteIds = $this->getSiteIds($nonProfitProjectIds);

        $forProfitTreeCount = $this->treeCountByDueDate($forProfitProjectIds);
        $nonProfitTreeCount = $this->treeCountByDueDate($nonProfitProjectIds);

        $totalTreesGrownGoal = $query->sum('trees_grown_goal');

        $treesUnderRestorationActualTotal = $this->treeCountPerPeriod($siteIds, $distinctDates, $totalTreesGrownGoal);
        $treesUnderRestorationActualForProfit = $this->treeCountPerPeriod($forProfitSiteIds, $distinctDates, $totalTreesGrownGoal);
        $treesUnderRestorationActualNonProfit = $this->treeCountPerPeriod($nonProfitSiteIds, $distinctDates, $totalTreesGrownGoal);

        $averageSurvivalRateTotal = $this->getAverageSurvival($allProjectIds);
        $averageSurvivalRateForProfit = $this->getAverageSurvival($forProfitProjectIds);
        $averageSurvivalRateNonProfit = $this->getAverageSurvival($nonProfitProjectIds);

        $result = [
            'forProfitTreeCount' => (int) $forProfitTreeCount,
            'nonProfitTreeCount' => (int) $nonProfitTreeCount,
            'totalTreesGrownGoal' => (int) $totalTreesGrownGoal,
            'treesUnderRestorationActualTotal' => $treesUnderRestorationActualTotal,
            'treesUnderRestorationActualForProfit' => $treesUnderRestorationActualForProfit,
            'treesUnderRestorationActualNonProfit' => $treesUnderRestorationActualNonProfit,
            'averageSurvivalRateTotal' => floatval($averageSurvivalRateTotal),
            'averageSurvivalRateForProfit' => floatval($averageSurvivalRateForProfit),
            'averageSurvivalRateNonProfit' => floatval($averageSurvivalRateNonProfit),
        ];

        return new JsonResponse(ViewTreeRestorationGoalResource::make($result));
    }

    private function prepareProjectQuery(Request $request)
    {
        return TerrafundDashboardQueryHelper::buildQueryFromRequest($request);
    }

    private function getRawProjectIds($query)
    {
        return $query
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->select('v2_projects.id', 'organisations.type')
            ->get();
    }

    private function getAllProjectIds($projectIds)
    {
        return $projectIds->pluck('id')->toArray();
    }

    private function getSiteIds($projectIds)
    {
        return Site::whereIn('project_id', $projectIds)->where('status', EntityStatusStateMachine::APPROVED)->pluck('id');
    }

    private function getDistinctDates($siteIds)
    {
        return SiteReport::selectRaw('YEAR(due_at) as year, MONTH(due_at) as month')
            ->whereNotNull('due_at')
            ->whereIn('site_id', $siteIds)
            ->groupBy('year', 'month')
            ->get()
            ->toArray();
    }

    private function filterProjectIdsByType($projectIds, $type)
    {
        return collect($projectIds)->filter(function ($row) use ($type) {
            return $row->type === $type;
        })->pluck('id')->toArray();
    }

    private function treeCountByDueDate(array $projectIds)
    {
        $projects = Project::whereIn('id', $projectIds)->get();

        return $projects->sum(function ($project) {
            return $project->trees_planted_count;
        });
    }

    private function treeCountPerPeriod($siteIds, $distinctDates, $totalTreesGrownGoal)
    {
        $treesUnderRestorationActual = [];
        $totalAmount = 0;

        foreach ($distinctDates as $date) {
            $year = $date['year'];
            $month = $date['month'];
            $treeSpeciesAmount = 0;

            $reports = SiteReport::whereIn('site_id', $siteIds)
                ->whereNotIn('v2_site_reports.status', SiteReport::UNSUBMITTED_STATUSES)
                ->whereYear('v2_site_reports.due_at', $year)
                ->whereMonth('v2_site_reports.due_at', $month)
                ->get();

            foreach ($reports as $report) {
                $treeSpeciesAmount += $report->treeSpecies()->where('collection', TreeSpecies::COLLECTION_PLANTED)->sum('amount');
            }

            $totalAmount = $totalTreesGrownGoal;

            $formattedDate = Carbon::create($year, $month, 1);

            $treesUnderRestorationActual[] = [
                'dueDate' => $formattedDate,
                'treeSpeciesAmount' => (int) $treeSpeciesAmount,
                'treeSpeciesPercentage' => 0,
            ];
        }

        foreach ($treesUnderRestorationActual as &$treeData) {
            $percentage = ($totalAmount != 0) ? ($treeData['treeSpeciesAmount'] / $totalAmount) * 100 : 0;
            $treeData['treeSpeciesPercentage'] = floatval(number_format($percentage, 3));
        }

        return $treesUnderRestorationActual;
    }

    private function getLatestDueDate($distinctDates)
    {
        $latestYear = 0;
        $latestMonth = 0;

        foreach ($distinctDates as $entry) {
            $year = $entry['year'];
            $month = $entry['month'];

            if ($year > $latestYear || ($year == $latestYear && $month > $latestMonth)) {
                $latestYear = $year;
                $latestMonth = $month;
            }
        }

        $latestDate = [
            'year' => $latestYear,
            'month' => $latestMonth,
        ];

        return $latestDate;
    }

    private function getAverageSurvival(array $projectIds)
    {
        return ProjectReport::hasBeenSubmittedOrApproved()->whereIn('project_id', $projectIds)->avg('pct_survival_to_date');
    }

}

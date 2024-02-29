<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewTreeRestorationGoalController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $organizationTypes = ['non-profit-organization', 'for-profit-organization'];
        $query = $this->prepareProjectQuery($request, $organizationTypes);
        $rawProjectIds = $this->getRawProjectIds($query);
        $allProjectIds = $this->getAllProjectIds($rawProjectIds);
        $siteIds = $this->getSiteIds($allProjectIds);
        $distinctDates = $this->getDistinctDates($siteIds);
        $latestDueDate = $this->getLatestDueDate($distinctDates);

        $forProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'for-profit-organization');
        $nonProfitProjectIds = $this->filterProjectIdsByType($rawProjectIds, 'non-profit-organization');
        $forProfitSiteIds = $this->getSiteIds($forProfitProjectIds);
        $nonProfitSiteIds = $this->getSiteIds($nonProfitProjectIds);

        $forProfitTreeCount = $this->treeCountByDueDate($forProfitProjectIds, $latestDueDate["year"], $latestDueDate["month"]);
        $nonProfitTreeCount = $this->treeCountByDueDate($nonProfitProjectIds, $latestDueDate["year"], $latestDueDate["month"]);

        $totalTreesGrownGoal = Project::sum('trees_grown_goal');

        $treesUnderRestorationActualTotal = $this->treeCountPerPeriod($siteIds, $distinctDates);
        $treesUnderRestorationActualForProfit = $this->treeCountPerPeriod($forProfitSiteIds, $distinctDates);
        $treesUnderRestorationActualNonProfit = $this->treeCountPerPeriod($nonProfitSiteIds, $distinctDates);

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

        return new JsonResponse($result);
    }

    private function prepareProjectQuery(Request $request, array $organizationTypes)
    {
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

        return $query;
    }

    private function getRawProjectIds($query)
    {
        return $query->select('v2_projects.id', 'organisations.type')->get();
    }

    private function getAllProjectIds($projectIds)
    {
        return $projectIds->pluck('id')->toArray();
    }

    private function getSiteIds($projectIds)
    {
        return Site::whereIn('project_id', $projectIds)->pluck('id');
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

    private function treeCountByDueDate(array $projectIds, $year, $month)
    {
        $siteIds = Site::whereIn('project_id', $projectIds)->pluck('id');
        $siteReportIds = SiteReport::whereIn('site_id', $siteIds)
            ->whereYear('due_at', $year)
            ->whereMonth('due_at', $month)
            ->pluck('id');

        $totalAmount = TreeSpecies::whereIn('speciesable_id', $siteReportIds)
            ->where('speciesable_type', SiteReport::class)
            ->sum('amount');

        return $totalAmount;
    }

    private function treeCountPerPeriod($siteIds, $distinctDates)
    {
        $treesUnderRestorationActual = [];
        $totalAmount = 0;

        foreach ($distinctDates as $date) {
            $year = $date["year"];
            $month = $date["month"];

            $treeSpeciesAmount = TreeSpecies::join('v2_site_reports', 'v2_tree_species.speciesable_id', '=', 'v2_site_reports.id')
                ->whereIn('v2_site_reports.site_id', $siteIds)
                ->where('v2_tree_species.speciesable_type', SiteReport::class)
                ->whereYear('v2_site_reports.due_at', $year)
                ->whereMonth('v2_site_reports.due_at', $month)
                ->sum('v2_tree_species.amount');

            $totalAmount += $treeSpeciesAmount;

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
            $year = $entry["year"];
            $month = $entry["month"];

            if ($year > $latestYear || ($year == $latestYear && $month > $latestMonth)) {
                $latestYear = $year;
                $latestMonth = $month;
            }
        }

        $latestDate = [
            'year' => $latestYear,
            'month' => $latestMonth
        ];

        return $latestDate;
    }

    private function getAverageSurvival(array $projectIds)
    {
        $averageSurvivalRate = ProjectReport::whereIn('project_id', $projectIds)->avg('pct_survival_to_date');
        return $averageSurvivalRate;
    }
}
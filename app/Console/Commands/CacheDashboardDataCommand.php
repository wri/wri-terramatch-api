<?php

namespace App\Console\Commands;

use App\Jobs\Dashboard\RunActiveCountriesTableJob;
use App\Jobs\Dashboard\RunActiveProjectsJob;
use App\Jobs\Dashboard\RunHectaresRestoredJob;
use App\Jobs\Dashboard\RunJobsCreatedJob;
use App\Jobs\Dashboard\RunTopTreesJob;
use App\Jobs\Dashboard\RunTotalHeaderJob;
use App\Jobs\Dashboard\RunTreeRestorationGoalJob;
use App\Jobs\Dashboard\RunVolunteersAverageJob;
use App\Models\DelayedJob;
use App\Models\Traits\HasCacheParameter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CacheDashboardDataCommand extends Command
{
    use HasCacheParameter;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:cache-data {--force : Force refresh all cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache all dashboard data for different filter permutations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting dashboard data caching process...');

        $landscapes = ['Greater Rift Valley of Kenya', 'Ghana Cocoa Belt', 'Lake Kivu & Rusizi River Basin'];
        $organizationTypes = ['non-profit-organization', 'for-profit-organization'];
        $cohorts = ['terrafund', 'terrafund-landscapes'];
        $programmes = [];

        $landscapeCombinations = $this->generateCombinations($landscapes);
        array_unshift($landscapeCombinations, []);

        $orgTypeCombinations = $this->generateCombinations($organizationTypes);
        array_unshift($orgTypeCombinations, []);

        $cohortCombinations = $this->generateCombinations($cohorts);
        array_unshift($cohortCombinations, []);

        $totalCombinations = count($landscapeCombinations) * count($orgTypeCombinations) * count($cohortCombinations);
        $this->info("Total combinations to process: {$totalCombinations}");

        $processed = 0;

        // Process all combinations
        foreach ($landscapeCombinations as $landscapeCombo) {
            foreach ($orgTypeCombinations as $orgTypeCombo) {
                foreach ($cohortCombinations as $cohortCombo) {
                    $this->processFilterCombination(
                        $programmes,
                        $landscapeCombo,
                        $orgTypeCombo,
                        '', // country
                        $cohortCombo ? implode(',', $cohortCombo) : '',
                        '' // uuid
                    );

                    $processed++;
                    if ($processed % 10 === 0 || $processed === $totalCombinations) {
                        $this->info("Processed {$processed} out of {$totalCombinations} combinations...");
                    }
                }
            }
        }

        $this->info('Dashboard data caching process completed!');

        return Command::SUCCESS;
    }

    /**
     * Generate all possible combinations of elements in an array
     *
     * @param array $items The array of items to generate combinations for
     * @return array An array of all possible combinations
     */
    private function generateCombinations(array $items): array
    {
        $combinations = [];
        $count = count($items);

        for ($i = 1; $i < (1 << $count); $i++) {
            $combo = [];
            for ($j = 0; $j < $count; $j++) {
                if ($i & (1 << $j)) {
                    $combo[] = $items[$j];
                }
            }
            $combinations[] = $combo;
        }

        return $combinations;
    }

    private function processFilterCombination(
        array $frameworks,
        array $landscapes,
        array $organisations,
        string $country,
        string $cohort,
        string $uuid
    ) {
        try {
            $cacheParameter = $this->getCacheParameter(
                $frameworks,
                $landscapes,
                $country,
                $organisations,
                $cohort,
                $uuid
            );

            $this->dispatchDashboardJobs(
                $cacheParameter,
                $frameworks,
                $landscapes,
                $organisations,
                $country,
                $cohort,
                $uuid
            );

        } catch (\Exception $e) {
            Log::error('Error in processing filter combination: ' . $e->getMessage());
            $this->error('Error processing combination: ' . $e->getMessage());
        }
    }

    private function dispatchDashboardJobs(
        string $cacheParameter,
        array $frameworks,
        array $landscapes,
        array $organisations,
        string $country,
        string $cohort,
        string $uuid
    ) {
        // Total Header Job
        $totalHeaderJob = DelayedJob::create();
        dispatch(new RunTotalHeaderJob(
            $totalHeaderJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));
        //Jobs Created Job
        $jobsCreatedJob = DelayedJob::create();
        dispatch(new RunJobsCreatedJob(
            $jobsCreatedJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));

        // Top Trees Job
        $topTreesJob = DelayedJob::create();
        dispatch(new RunTopTreesJob(
            $topTreesJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));

        // Active Countries Job
        $activeCountriesDelayedJob = DelayedJob::create();
        dispatch(new RunActiveCountriesTableJob(
            $activeCountriesDelayedJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));

        // Active Projects Job
        $activeProjectsDelayedJob = DelayedJob::create();
        dispatch(new RunActiveProjectsJob(
            $activeProjectsDelayedJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));

        // Tree Restoration Goal Job
        $treeRestorationGoalDelayedJob = DelayedJob::create();
        dispatch(new RunTreeRestorationGoalJob(
            $treeRestorationGoalDelayedJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));

        // Volunteers Job
        $volunteersDelayedJob = DelayedJob::create();
        dispatch(new RunVolunteersAverageJob(
            $volunteersDelayedJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));

        // Hectares Restored Job
        $hectaresRestoredDelayedJob = DelayedJob::create();
        dispatch(new RunHectaresRestoredJob(
            $hectaresRestoredDelayedJob->id,
            $frameworks,
            $landscapes,
            $organisations,
            $country,
            $cohort,
            $uuid,
            $cacheParameter
        ));
    }
}

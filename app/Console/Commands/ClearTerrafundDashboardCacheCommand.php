<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearTerrafundDashboardCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-terrafund-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Terrafund Dashboard cache';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cacheKeys = [
            'dashboard:active-countries-table|*',
            'dashboard:active-projects|*',
            'dashboard:jobs-created|*',
            'dashboard:top-trees-planted|*',
            'dashboard:total-section-header|*',
            'dashboard:tree-restoration-goal|*',
            'dashboard:volunteers-survival-rate|*',
            'dashboard:indicator/hectares-restoration*',
        ];

        foreach ($cacheKeys as $key) {
            $redisKeys = Redis::keys($key);
            foreach ($redisKeys as $redisKey) {
                Redis::del($redisKey);
                $this->info("Deleted cache key: {$redisKey}");
            }
        }

        $this->info('Terrafund Dashboard cache cleared successfully.');
        return 0;
    }
}
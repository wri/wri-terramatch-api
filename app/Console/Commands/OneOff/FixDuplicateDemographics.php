<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Workdays\Workday;
use App\Models\V2\Workdays\WorkdayDemographic;
use Illuminate\Console\Command;

class FixDuplicateDemographics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:fix-duplicate-demographics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes workday demographics that have been duplicated';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workdays = [];

        WorkdayDemographic::selectRaw('workday_id, type, subtype, name, count(*) as num, sum(amount) as sum')
            ->groupBy('workday_id', 'type', 'subtype', 'name')
            ->orderByRaw('num desc')
            ->chunk(10, function ($chunk) use (&$workdays) {
                foreach ($chunk as $demographic) {
                    if ($demographic->num == 1) {
                        return false;
                    }

                    $demographic['amounts'] = WorkdayDemographic::where([
                        'workday_id' => $demographic->workday_id,
                        'type' => $demographic->type,
                        'subtype' => $demographic->subtype,
                        'name' => $demographic->name,
                    ])->pluck('amount');
                    $workdays[$demographic->workday_id]['demographics'][] = $demographic;
                }

                return true;
            });

        foreach ($workdays as $workdayId => &$stats) {
            $workday = Workday::find($workdayId);
            $stats['workdayable_type'] = $workday->workdayable_type;
            $stats['workdayable_id'] = $workday->workdayable_id;
            $stats['workdayable_uuid'] = $workday->workdayable->uuid;
            $stats['collection'] = $workday->collection;
        }

        $this->info(json_encode($workdays, JSON_PRETTY_PRINT));
    }
}

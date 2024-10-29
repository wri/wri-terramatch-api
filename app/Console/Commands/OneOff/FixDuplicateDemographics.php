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
            // group by is case insensitive, so in order to avoid turning up false positives, we cast to binary
            // to get the DB to recognize different casing.
            ->groupByRaw('workday_id, type, subtype, name, Cast(name as binary)')
            ->orderByRaw('num desc')
            ->chunk(10, function ($chunk) use (&$workdays) {
                foreach ($chunk as $demographic) {
                    if ($demographic->num == 1) {
                        return false;
                    }

                    $demographic['rows'] = WorkdayDemographic::where([
                        'workday_id' => $demographic->workday_id,
                        'type' => $demographic->type,
                        'subtype' => $demographic->subtype,
                        'name' => $demographic->name,
                    ])->select('id', 'amount')->get()->toArray();
                    $workdays[$demographic->workday_id][] = $demographic->toArray();
                }

                return true;
            });

        foreach ($workdays as $workdayId => $stats) {
            foreach ($stats as $stat) {
                $workday = Workday::withTrashed()->find($workdayId);

                $rows = collect($stat['rows']);
                $max = $rows->max('amount');
                $allEqual = $rows->unique('amount')->count() == 1;
                $hasKept = false;
                foreach ($stat['rows'] as $index => &$row) {
                    if ($hasKept) {
                        $row['action'] = 'delete';

                        continue;
                    }

                    if (($allEqual && $index == 0) || (! $allEqual && $row['amount'] == $max)) {
                        $row['action'] = 'keep';
                        $hasKept = true;
                    } else {
                        $row['action'] = 'delete';
                    }
                }

                $info = [
                    'workday_id' => $workdayId,
                    'workdayable_type' => $workday->workdayable_type,
                    'workdayable_id' => $workday->workdayable_id ,
                    'workdayable_uuid' => $workday->workdayable->uuid ,
                    'collection' => $workday->collection ,
                    'type' => $stat['type'],
                    'subtype' => $stat['subtype'],
                    'name' => $stat['name'],
                    'rows' => $stat['rows'],
                ];
                if (! $hasKept) {
                    $this->error('No demographic to keep found! ' . json_encode($info, JSON_PRETTY_PRINT));
                    exit(1);
                }

                $this->info('Fixing demographics: ' . json_encode($info, JSON_PRETTY_PRINT) . "\n");
                unset($row);
                foreach ($stat['rows'] as $row) {
                    if ($row['action'] == 'delete') {
                        WorkdayDemographic::find($row['id'])->delete();
                    }
                }
            }
        }

        $this->info('Data fix complete!');
    }
}

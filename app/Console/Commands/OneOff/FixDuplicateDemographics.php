<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicEntry;
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
    protected $description = 'Fixes demographics that have been duplicated';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $demographics = [];

        DemographicEntry::selectRaw('demographic_id, type, subtype, name, count(*) as num, sum(amount) as sum')
            // group by is case insensitive, so in order to avoid turning up false positives, we cast to binary
            // to get the DB to recognize different casing.
            ->groupByRaw('demographic_id, type, subtype, name, Cast(name as binary)')
            ->orderByRaw('num desc')
            ->chunk(10, function ($chunk) use (&$demographics) {
                foreach ($chunk as $entry) {
                    if ($entry->num == 1) {
                        return false;
                    }

                    $entry['rows'] = DemographicEntry::where([
                        'demographic_id' => $entry->demographic_id,
                        'type' => $entry->type,
                        'subtype' => $entry->subtype,
                        'name' => $entry->name,
                    ])->select('id', 'amount')->get()->toArray();
                    $demographics[$entry->demographic_id][] = $entry->toArray();
                }

                return true;
            });

        foreach ($demographics as $demographicId => $stats) {
            foreach ($stats as $stat) {
                /** @var Demographic $demographic */
                $demographic = Demographic::withTrashed()->find($demographicId);

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
                    'demographic_id' => $demographicId,
                    'demographical_type' => $demographic->demographical_type,
                    'demographical_id' => $demographic->demographical_id,
                    'demographical_uuid' => $demographic->demographical()->withTrashed()->first()->uuid,
                    'demographic_type' => $demographic->type,
                    'collection' => $demographic->collection,
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
                        DemographicEntry::find($row['id'])->delete();
                    }
                }
            }
        }

        $this->info('Data fix complete!');
    }
}

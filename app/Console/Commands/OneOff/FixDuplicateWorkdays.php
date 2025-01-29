<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Models\V2\Workdays\Workday;
use Illuminate\Console\Command;

class FixDuplicateWorkdays extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:fix-duplicate-workdays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes instances of duplicated Workdays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->executeAbortableScript(function () {
            # Find all instances of workdays that are duplicated across the same type, id and collection.
            $dupes = Workday::select('workdayable_type', 'workdayable_id', 'collection')
                ->groupBy('workdayable_type', 'workdayable_id', 'collection')
                ->havingRaw('count(*) > ?', [1])
                ->get();

            $errors = [];
            foreach ($dupes as $dupe) {
                try {
                    $this->removeDuplicates($dupe);
                } catch (AbortException $e) {
                    $errors[] = $e;
                }
            }

            $this->info("Completed processing {$dupes->count()} duplicated workdays\n\n");

            if (count($errors) > 0) {
                $this->warn('Entries that were not resolved: ');
                foreach ($errors as $error) {
                    $this->warn($error->getMessage());
                }
            }
        });
    }

    /**
     * @throws AbortException
     */
    private function removeDuplicates($dupe)
    {
        $workdays = Workday::where([
            'workdayable_type' => $dupe->workdayable_type,
            'workdayable_id' => $dupe->workdayable_id,
            'collection' => $dupe->collection,
        ])->get();

        $type = explode('\\', $dupe->workdayable_type);
        $params = json_encode([
            'type' => array_pop($type),
            'id' => $dupe->workdayable_id,
            'uuid' => $workdays->first()->workdayable->uuid,
            'collection' => $dupe->collection,
        ]);
        $this->assert(
            $workdays->map(fn ($w) => $w->visible)->unique()->count() == 1,
            "Visible not identical: $params"
        );
        $this->assert(
            $workdays->map(fn ($w) => $w->description)->unique()->count() == 1,
            "Description not identical: $params"
        );

        // Some of these workdays have had updates. This check makes sure that either all the demographics have the
        // same updated stamp (meaning that none have been updated, or they were all updated together), or that if
        // there is something updated, the first one on the list is the most recently updated one, as we would expect.
        $mostRecentUpdated = $workdays->sortBy('updated_at')->last();
        $numberUpdatedDates = $workdays->map(fn ($w) => $w->updated_at)->unique()->count();
        $this->assert($numberUpdatedDates == 1 || $mostRecentUpdated == $workdays->first(), "First is not the most recently updated: $params");

        // If we made it through the checks above, it's considered safe to delete all the workdays after the first one.
        $this->info("Removing dupes: [$dupe->workdayable_type, $dupe->workdayable_id, $dupe->collection]");
        foreach ($workdays->slice(1) as $workday) {
            $workday->demographics()->delete();
            $workday->delete();
        }
    }
}

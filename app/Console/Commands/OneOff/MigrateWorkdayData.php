<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Workdays\Workday;
use App\Models\V2\Workdays\WorkdayDemographic;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateWorkdayData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-workday-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Workday data to use the new workday_demographics table';

    private const DEMOGRAPHICS = [
        WorkdayDemographic::GENDER,
        WorkdayDemographic::AGE,
        WorkdayDemographic::ETHNICITY,
    ];

    private const SUBTYPE_NULL = 'subtype-null';
    private const NAME_NULL = 'name-null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $entityTypes = Workday::select('workdayable_type')->distinct()->pluck('workdayable_type');
        foreach ($entityTypes as $entityType) {
            $entityIds = Workday::where('workdayable_type', $entityType)
                ->select('workdayable_id')
                ->distinct()
                ->pluck('workdayable_id');
            $count = $entityIds->count();
            $shortName = explode_pop('\\', $entityType);
            echo "Processing $shortName: $count records\n";

            foreach ($entityIds as $entityId) {
                $this->updateEntityWorkdays($entityType, $entityId);
            }
        }
    }

    private function updateEntityWorkdays(string $entityType, int $entityId): void
    {
        $workdayCollections = Workday::where(['workdayable_type' => $entityType, 'workdayable_id' => $entityId])
            ->get()
            ->reduce(function (array $carry, Workday $workday) {
                $carry[$workday['collection']][] = $workday;

                return $carry;
            }, []);

        foreach ($workdayCollections as $collection => $workdays) {
            $mapping = $this->mapWorkdayCollection($workdays);
            $framework_key = $workdays[0]->framework_key;

            $workday = Workday::create([
                'workdayable_type' => $entityType,
                'workdayable_id' => $entityId,
                'framework_key' => $framework_key,
                'collection' => $collection,
            ]);
            foreach ($mapping as $demographic => $subTypes) {
                foreach ($subTypes as $subType => $names) {
                    foreach ($names as $name => $amount) {
                        WorkdayDemographic::create([
                            'workday_id' => $workday->id,
                            'type' => $demographic,
                            'subtype' => $subType == self::SUBTYPE_NULL ? null : $subType,
                            'name' => $name == self::NAME_NULL ? null : $name,
                            'amount' => $amount,
                        ]);
                    }
                }
            }

            $workdayIds = collect($workdays)->map(fn ($workday) => $workday->id)->all();
            Workday::whereIn('id', $workdayIds)->update(['migrated_to_demographics' => true]);
            Workday::whereIn('id', $workdayIds)->delete();
        }
    }

    private function mapWorkdayCollection(array $workdays): array
    {
        $demographics = [];
        foreach (self::DEMOGRAPHICS as $demographic) {
            foreach ($workdays as $workday) {
                $subType = $this->getSubtype($demographic, $workday);
                $name = match ($workday[$demographic]) {
                    null, 'gender-undefined', 'age-undefined', 'decline-to-specify' => 'unknown',
                    default => $workday[$demographic],
                };
                if ($subType == 'unknown' && strcasecmp($name, 'unknown') == 0) {
                    // We only get an unknown subtype when we're working on ethnicity. If the value is also unknown in
                    // this case, we want to leave it null.
                    $name = self::NAME_NULL;
                }

                $current = data_get($demographics, "$demographic.$subType.$name");
                data_set($demographics, "$demographic.$subType.$name", $current + $workday->amount);
            }
        }

        return $demographics;
    }

    private function getSubtype(string $demographic, Workday $workday): string
    {
        if ($demographic != WorkdayDemographic::ETHNICITY) {
            return self::SUBTYPE_NULL;
        }

        if ($workday->indigeneity != null && $workday->indigeneity != 'decline to specify') {
            return $workday->indigeneity;
        }

        if (Str::startsWith($workday->ethnicity, 'indigenous')) {
            return 'indigenous';
        } elseif (Str::startsWith($workday->ethnicity, 'other')) {
            return 'other';
        }

        return 'unknown';
    }
}

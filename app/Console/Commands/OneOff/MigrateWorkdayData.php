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
    private const VALUE_NULL = 'value-null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = [
            $this->testCase(\App\Models\V2\Projects\ProjectReport::class, 783),
            $this->testCase(\App\Models\V2\Sites\SiteReport::class, 15109),
            $this->testCase(\App\Models\V2\Projects\ProjectReport::class, 784),
            $this->testCase(\App\Models\V2\Sites\SiteReport::class, 13965),
            $this->testCase(\App\Models\V2\Projects\ProjectReport::class, 763),
            $this->testCase(\App\Models\V2\Sites\SiteReport::class, 15170),
            $this->testCase(\App\Models\V2\Sites\SiteReport::class, 15400),
            $this->testCase(\App\Models\V2\Sites\SiteReport::class, 13805),
            $this->testCase(\App\Models\V2\Projects\ProjectReport::class, 778),
        ];
        echo json_encode($result, JSON_PRETTY_PRINT);


        // TODO (NJC): The code below will be needed when this has been updated to perform the actual migration instead
        //   of just testing a few isolated cases and dumping a JSON result.
        //        $entityTypes = Workday::select('workdayable_type')->distinct()->pluck('workdayable_type');
        //        foreach ($entityTypes as $entityType) {
        //            $entityIds = Workday::where('workdayable_type', $entityType)
        //                ->select('workdayable_id')
        //                ->distinct()
        //                ->pluck('workdayable_id');
        //            $count = $entityIds->count();
        //            $shortName = explode_pop('\\', $entityType);
        //            echo "Processing $shortName: $count records\n";
        //
        //            foreach ($entityIds as $entityId) {
        //                $this->updateEntity($entityType, $entityId);
        //            }
        //        }
    }

    private function testCase(string $entityType, int $entityId): array
    {
        return [
            'type' => $entityType,
            'id' => $entityId,
            'uuid' => $entityType::find($entityId)->uuid,
            'mapping' => $this->updateEntityWorkdays($entityType, $entityId),
        ];
    }

    private function updateEntityWorkdays(string $entityType, int $entityId): array
    {
        $workdayCollections = Workday::where(['workdayable_type' => $entityType, 'workdayable_id' => $entityId])
            ->get()
            ->reduce(function (array $carry, Workday $workday) {
                $carry[$workday['collection']][] = $workday;

                return $carry;
            }, []);

        $results = [];
        foreach ($workdayCollections as $collection => $workdays) {
            $results[$collection] = $this->mapWorkdayCollection($workdays);
        }

        return $results;
    }

    private function mapWorkdayCollection(array $workdays): array
    {
        $results = ['original' => collect($workdays)->map(function ($workday) {
            return [
                'amount' => $workday->amount,
                'gender' => $workday->gender,
                'age' => $workday->age,
                'ethnicity' => $workday->ethnicity,
                'indigeneity' => $workday->indigeneity,
            ];
        })];

        $demographics = [];
        foreach (self::DEMOGRAPHICS as $demographic) {
            foreach ($workdays as $workday) {
                $subType = $this->getSubtype($demographic, $workday);
                $value = match ($workday[$demographic]) {
                    null, 'gender-undefined', 'age-undefined' => 'unknown',
                    default => $workday[$demographic],
                };
                if ($subType == 'unknown' && strcasecmp($value, 'unknown') == 0) {
                    // We only get an unknown subtype when we're working on ethnicity. If the value is also unknown in
                    // this case, we want to leave it null.
                    $value = self::VALUE_NULL;
                }

                $current = data_get($demographics, "$demographic.$subType.$value.amount");
                data_set($demographics, "$demographic.$subType.$value.amount", $current + $workday->amount);
            }
        }

        $results['new-workday-demographics'] = $demographics;

        return $results;
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

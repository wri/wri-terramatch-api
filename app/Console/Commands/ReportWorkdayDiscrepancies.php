<?php

namespace App\Console\Commands;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;

class ReportWorkdayDiscrepancies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report-workday-discrepancies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reports on project and site reports that have a difference between aggregated and disaggregated workday numbers';

    private const PROPERTIES = [
        ProjectReport::class => [
            'paid' => [
                DemographicCollections::PAID_NURSERY_OPERATIONS,
                DemographicCollections::PAID_PROJECT_MANAGEMENT,
                DemographicCollections::PAID_OTHER,
            ],
            'volunteer' => [
                DemographicCollections::VOLUNTEER_NURSERY_OPERATIONS,
                DemographicCollections::VOLUNTEER_PROJECT_MANAGEMENT,
                DemographicCollections::VOLUNTEER_OTHER,
            ],
        ],
        SiteReport::class => [
            'paid' => [
                DemographicCollections::PAID_SITE_ESTABLISHMENT,
                DemographicCollections::PAID_PLANTING,
                DemographicCollections::PAID_SITE_MAINTENANCE,
                DemographicCollections::PAID_SITE_MONITORING,
                DemographicCollections::PAID_OTHER,
            ],
            'volunteer' => [
                DemographicCollections::VOLUNTEER_SITE_ESTABLISHMENT,
                DemographicCollections::VOLUNTEER_PLANTING,
                DemographicCollections::VOLUNTEER_SITE_MAINTENANCE,
                DemographicCollections::VOLUNTEER_SITE_MONITORING,
                DemographicCollections::VOLUNTEER_OTHER,
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Model Type,Model UUID,Aggregate Paid Total,Disaggregate Paid Total,Aggregate Volunteer Total,Disaggregate Volunteer Total\n";
        foreach (self::PROPERTIES as $model => $propertySets) {
            $model::where('status', 'approved')->chunkById(
                100,
                function ($reports) use ($propertySets) {
                    foreach ($reports as $report) {
                        $aggregate_paid = (int)$report->workdays_paid;
                        $aggregate_volunteer = (int)$report->workdays_volunteer;

                        $modelType = get_class($report);
                        $query = Demographic::where([
                            'demographical_type' => $modelType,
                            'demographical_id' => $report->id,
                            'type' => Demographic::WORKDAY_TYPE,
                        ]);
                        if ($query->count() == 0) {
                            // Skip reports that have no associated workday rows.
                            continue;
                        }

                        $disaggregate_paid = (int)(clone $query)->whereIn('collection', $propertySets['paid'])->sum('amount');
                        $disaggregate_volunteer = (int)(clone $query)->whereIn('collection', $propertySets['volunteer'])->sum('amount');

                        if ($aggregate_paid != $disaggregate_paid || $aggregate_volunteer != $disaggregate_volunteer) {
                            $shortType = explode_pop('\\', $modelType);
                            echo "$shortType,$report->uuid,$aggregate_paid,$disaggregate_paid,$aggregate_volunteer,$disaggregate_volunteer\n";
                        }
                    }
                }
            );
        }
    }
}

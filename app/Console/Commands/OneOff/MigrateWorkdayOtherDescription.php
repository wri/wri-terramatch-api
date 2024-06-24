<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;

class MigrateWorkdayOtherDescription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-workday-other-description';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves workday other description data from the reports to the workdays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach ([SiteReport::class, ProjectReport::class] as $reportClass) {
            $query = $reportClass::whereNot('paid_other_activity_description', null);
            echo "Updating $reportClass, instance count: " . (clone $query)->count() . "\n";

            foreach ($query->get() as $report) {
                $report->update(['other_workdays_description' => $report->paid_other_activity_description]);
            }
        }

        echo "Migration complete!\n";
    }
}

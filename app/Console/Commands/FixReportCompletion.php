<?php

namespace App\Console\Commands;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;

class FixReportCompletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-report-completion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes reports that have a completion of 0 despite being in approved or awaiting-approval status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ProjectReport::withoutTimestamps(function () {
            $projectReportsUpdated = ProjectReport::withTrashed()
                ->whereIn('status', [ProjectReport::STATUS_APPROVED, ProjectReport::STATUS_AWAITING_APPROVAL])
                ->where('completion', 0)
                ->update([
                    'completion' => 100,
                    'completion_status' => ProjectReport::COMPLETION_STATUS_COMPLETE
                ]);
            $this->info("Project Reports Updated: $projectReportsUpdated");
        });

        SiteReport::withoutTimestamps(function () {
            $siteReportsUpdated = SiteReport::withTrashed()
                ->whereIn('status', [SiteReport::STATUS_APPROVED, SiteReport::STATUS_AWAITING_APPROVAL])
                ->where('completion', 0)
                ->update([
                    'completion' => 100,
                    'completion_status' => SiteReport::COMPLETION_STATUS_COMPLETE
                ]);
            $this->info("Site Reports Updated: $siteReportsUpdated");
        });

        NurseryReport::withoutTimestamps(function () {
            $nurseryReportsUpdated = NurseryReport::withTrashed()
                ->whereIn('status', [NurseryReport::STATUS_APPROVED, NurseryReport::STATUS_AWAITING_APPROVAL])
                ->where('completion', 0)
                ->update([
                    'completion' => 100,
                    'completion_status' => NurseryReport::COMPLETION_STATUS_COMPLETE
                ]);
            $this->info("Nursery Reports Updated: $nurseryReportsUpdated");
        });
    }
}

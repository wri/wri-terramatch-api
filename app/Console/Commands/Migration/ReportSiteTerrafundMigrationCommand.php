<?php

namespace App\Console\Commands\Migration;

use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Console\Command;

class ReportSiteTerrafundMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:report-site-terrafund {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Terrafund Site Submission Data only to  V2 Site reports';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            SiteReport::truncate();
        }

        TerrafundSiteSubmission::chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $submission) {
                $count++;
                $map = $this->mapValues($submission);

                $report = SiteReport::create($map);
                if (! empty($report)) {
                    $created++;
                }

                if ($this->option('timestamps')) {
                    $report->created_at = $submission->created_at;
                    $report->updated_at = $submission->updated_at;
                    $report->save();
                }
            }
        });

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapValues(TerrafundSiteSubmission $submission): array
    {
        $data = [
            'old_model' => TerrafundSiteSubmission::class,
            'old_id' => $submission->id,
            'framework_key' => 'terrafund',

            'status' => ReportStatusStateMachine::AWAITING_APPROVAL,
            'due_at' => $this->handleDueAt($submission),
            'shared_drive_link' => data_get($submission, 'shared_drive_link'),
            'submitted_at' => data_get($submission, 'created_at'),
        ];

        $site = Site::where('old_model', TerrafundSite::class)
            ->where('old_id', $submission->terrafund_site_id)
            ->first();

        if (! empty($site)) {
            $data['site_id'] = $site->id;
        }

        return $data;
    }

    private function handleDueAt(TerrafundSiteSubmission $submission)
    {
        if (empty($submission->terrafundDueSubmission)) {
            return null;
        }

        return $submission->terrafundDueSubmission->due_at;
    }
}

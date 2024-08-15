<?php

namespace App\Console\Commands\Migration;

use App\Models\Site as PPCSite;
use App\Models\SiteSubmission;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Console\Command;

class ReportSitePPCMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:report-site-ppc {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate PPC Site Submission Data only to  V2 Site reports';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            SiteReport::truncate();
        }

        SiteSubmission::chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $submission) {
                $count++;
                $map = $this->mapValues($submission);

                $report = SiteReport::create($map);
                $created++;

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

    private function mapValues(SiteSubmission $submission): array
    {
        $data = [
            'old_model' => SiteSubmission::class,
            'old_id' => $submission->id,
            'framework_key' => 'ppc',

            'due_at' => $this->handleDueAt($submission),
            'status' => ReportStatusStateMachine::APPROVED,
            'approved_at' => data_get($submission, 'approved_at'),
            'approved_by' => data_get($submission, 'approved_by'),
            'created_by' => $this->findUserFromName(data_get($submission, 'created_by')),
            'workdays_paid' => data_get($submission, 'workdays_paid'),
            'workdays_volunteer' => data_get($submission, 'workdays_volunteer'),
            'technical_narrative' => data_get($submission, 'technical_narrative'),
            'public_narrative' => data_get($submission, 'public_narrative'),
            'submitted_at' => data_get($submission, 'created_at'),
        ];

        $site = Site::where('old_model', PPCSite::class)
            ->where('old_id', $submission->site_id)
            ->first();

        if (! empty($site)) {
            $data['site_id'] = $site->id;
        }

        return $data;
    }

    private function handleDueAt(SiteSubmission $submission)
    {
        if (empty($submission->dueSubmission)) {
            return null;
        }

        return $submission->dueSubmission->due_at;
    }

    private function findUserFromName($name): ?int
    {
        if (empty($name)) {
            return null;
        }

        $parts = explode(' ', $name);
        if (count($parts) == 2) {
            $count = User::where('first_name', $parts[0])
                ->where('last_name', $parts[1])
                ->count();
            if ($count == 1) {
                $user = User::where('first_name', $parts[0])
                    ->where('last_name', $parts[1])
                    ->first();

                return empty($user) ? null : $user->id;
            }
        }

        return null;
    }
}

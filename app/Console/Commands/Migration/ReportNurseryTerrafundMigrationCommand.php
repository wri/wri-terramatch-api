<?php

namespace App\Console\Commands\Migration;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Console\Command;

class ReportNurseryTerrafundMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:report-nursery-terrafund {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Terrafund Nursery Submission Data only to  V2 Nursery reports';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            NurseryReport::truncate();
        }

        TerrafundNurserySubmission::chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $submission) {
                $count++;
                $map = $this->mapValues($submission);

                $report = NurseryReport::create($map);
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

    private function mapValues(TerrafundNurserySubmission $submission): array
    {
        $data = [
            'old_model' => TerrafundNurserySubmission::class,
            'old_id' => $submission->id,
            'framework_key' => 'terrafund',

            'due_at' => $this->handleDueAt($submission),
            'status' => ReportStatusStateMachine::AWAITING_APPROVAL,
            'seedlings_young_trees' => data_get($submission, 'seedlings_young_trees'),
            'interesting_facts' => data_get($submission, 'interesting_facts'),
            'site_prep' => data_get($submission, 'site_prep'),
            'shared_drive_link' => data_get($submission, 'shared_drive_link'),
            'submitted_at' => data_get($submission, 'created_at'),

        ];

        $nursery = Nursery::where('old_model', TerrafundNursery::class)
            ->where('old_id', $submission->terrafund_nursery_id)
            ->first();

        if (! empty($nursery)) {
            $data['nursery_id'] = $nursery->id;
        }

        return $data;
    }

    private function handleDueAt(TerrafundNurserySubmission $submission)
    {
        if (empty($submission->terrafundDueSubmission)) {
            return null;
        }

        return $submission->terrafundDueSubmission->due_at;
    }
}

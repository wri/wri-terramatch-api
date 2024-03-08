<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\Submission;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Console\Command;

class ReportProjectPPCMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:report-project-ppc {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate PPC Submission Data only to  V2 Project reports';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            ProjectReport::truncate();
        }

        Submission::chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $submission) {
                $count++;
                $map = $this->mapValues($submission);

                $report = ProjectReport::create($map);
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

    private function mapValues(Submission $submission): array
    {
        $data = [
            'old_model' => Submission::class,
            'old_id' => $submission->id,
            'framework_key' => 'ppc',
            'due_at' => $this->handleDueAt($submission),
            'created_by' => $this->findUserFromName(data_get($submission, 'created_by'), $submission->programme),
            'approved_by' => data_get($submission, 'approved_by'),
            'status' => ReportStatusStateMachine::APPROVED,
            'title' => data_get($submission, 'title'),
            'workdays_paid' => data_get($submission, 'workdays_paid'),
            'workdays_volunteer' => data_get($submission, 'workdays_volunteer'),
            'technical_narrative' => data_get($submission, 'technical_narrative'),
            'public_narrative' => data_get($submission, 'public_narrative'),
            'submitted_at' => data_get($submission, 'created_at'),
        ];

        $project = Project::where('old_model', Programme::class)
            ->where('old_id', $submission->programme_id)
            ->first();

        if (! empty($project)) {
            $data['project_id'] = $project->id;
        }

        return $data;
    }

    private function handleDueAt(Submission $submission)
    {
        if (empty($submission->dueSubmission)) {
            return null;
        }

        return $submission->dueSubmission->due_at;
    }

    private function findUserFromName($name, $programme): ?int
    {
        if (empty($name)) {
            return null;
        }

        if (! empty($programme)) {
            foreach ($programme->users as $user) {
                $recordName = trim(strtolower($user->first_name)) . ' ' . trim(strtolower($user->last_name));
                if ($recordName == trim(strtolower($name))) {
                    return $user->id;
                }
            }
        }

        return null;

        /*
            $parts = explode(' ', $name);
            if (count($parts) == 2) {
                $user = User::where('first_name', $parts[0])
                    ->where('last_name', $parts[1])
                    ->first();
            }

            return ! empty($user) ? $user->id : null;
        */
    }
}

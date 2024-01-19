<?php

namespace App\Jobs\V2;

use App\Models\V2\Action;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateProjectReportDueJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?Carbon $dueDate;

    private $frameworkKey;

    public function __construct(string $frameworkKey, int $dueMonth = null)
    {
        $this->frameworkKey = $frameworkKey;

        if ($dueMonth) {
            $carbonDate = Carbon::createFromFormat('m', $dueMonth);
            $this->dueDate = $carbonDate->isPast() ? $carbonDate->addYear()->firstOfMonth(5) : $carbonDate->firstOfMonth(5);
        } else {
            $this->dueDate = Carbon::now()->addMonth()->startOfDay();
        }
    }

    public function handle()
    {
        Project::where('framework_key', $this->frameworkKey)
            ->chunkById(100, function ($projects) {
                foreach ($projects as $project) {
                    $report = ProjectReport::create([
                        'framework_key' => $this->frameworkKey,
                        'project_id' => $project->id,
                        'status' => ProjectReport::STATUS_DUE,
                        'due_at' => $this->dueDate,
                    ]);

                    Task::create([
                        'organisation_id' => $project->organisation_id,
                        'project_id' => $project->id,
                        'status' => Task::STATUS_DUE,
                        'period_key' => $this->dueDate->year . '-' . $this->dueDate->month,
                        'due_at' => $this->dueDate,
                    ]);

                    $nurseryCount = $project->nurseries()->count();
                    $siteCount = $project->sites()->count();
                    if ($nurseryCount != 0 && $siteCount != 0) {
                        $message = 'Project, site and nursery reports available';
                    } elseif ($nurseryCount > 0) {
                        $message = 'Project and nursery reports available';
                    } elseif ($siteCount > 0) {
                        $message = 'Project and site reports available';
                    } else {
                        $message = 'Project report available';
                    }

                    Action::create([
                        'status' => Action::STATUS_PENDING,
                        'targetable_type' => ProjectReport::class,
                        'targetable_id' => $report->id,
                        'type' => Action::TYPE_NOTIFICATION,
                        'title' => 'Project report',
                        'sub_title' => '',
                        'text' => $message,
                        'project_id' => $project->id,
                        'organisation_id' => data_get($project->organisation, 'id'),
                    ]);
                }
            });
    }
}

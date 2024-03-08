<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Mavinoo\Batch\BatchFacade;

class AssociateReportsTasksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:associate-reports-and-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all ProgramReports, SiteReports and NurseryReports to have a valid Task association';

    public function handle()
    {
        $this->addTaskIds(ProjectReport::class);
        $this->addTaskIds(
            SiteReport::class,
            fn ($sr) => $sr->site()->withTrashed()->first()->project()->withTrashed()->first()
        );
        $this->addTaskIds(
            NurseryReport::class,
            fn ($nr) => $nr->nursery()->withTrashed()->first()->project()->withTrashed()->first()
        );
    }

    protected function addTaskIds ($class, $projectGetter = null): void
    {
        if ($projectGetter == null) {
            $projectGetter = fn ($report) => $report->project()->withTrashed()->first();
        }
        $className = array_slice(explode('\\', $class), -1)[0];
        $this->info("Beginning $className migration...");

        $updated = 0;
        $skipped = 0;

        $query = $class::withTrashed()->whereNot('due_at', null)->where('task_id', null)->orderBy('id');
        $target = $query->count();
        $query->chunkById(100, function ($reports) use ($projectGetter, $class, &$updated, &$skipped) {
            $batchValues = [];

            foreach ($reports as $report) {
                $project = $projectGetter($report);
                if ($project == null) {
                    $skipped++;
                    continue;
                }

                $task = $this->taskForProjectAndDate($project, $report->due_at)->first();
                if ($task == null) {
                    $skipped++;
                    continue;
                }

                $batchValues[] = [ 'id' => $report->id, 'task_id' => $task->id ];
            }

            $class::withoutTimestamps(function () use ($class, $batchValues) {
                BatchFacade::update(new $class, $batchValues, 'id');
            });
            $updated += count($batchValues);
        });

        $this->info("Completed $className migration [target=$target, updated=$updated, skipped=$skipped]");
    }

    protected function taskForProjectAndDate (Project $project, Carbon $date): Builder
    {
        return Task::where('project_id', $project->id)
            ->whereMonth('due_at', $date->month)
            ->whereYear('due_at', $date->year);
    }
}

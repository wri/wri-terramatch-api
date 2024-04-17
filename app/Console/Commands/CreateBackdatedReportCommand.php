<?php

namespace App\Console\Commands;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Illuminate\Console\Command;

class CreateBackdatedReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-backdated-report {--T|type=} {uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a report for a specific site or nursery';

    public function handle(): int
    {
        $type = $this->option('type');
        switch ($type) {
            case 'project':
                $entityModel = Project::class;
                $reportModel = ProjectReport::class;

                break;

            case 'site':
                $entityModel = Site::class;
                $reportModel = SiteReport::class;

                break;

            case 'nursery':
                $entityModel = Nursery::class;
                $reportModel = NurseryReport::class;

                break;

            default:
                $this->error('Type must be one of "site" or "nursery"');

                return 1;
        }

        $uuid = $this->argument('uuid');

        $entity = $entityModel::where('uuid', $uuid)->first();
        if ($entity == null) {
            $this->error("Entity.php not found [type=$type, uuid=$uuid]");

            return 1;
        }

        $task = Task::withTrashed()->where('project_id', $entity->project_id)->latest()->first();
        if ($task == null) {
            $this->error("Task not found for project [$entity->project_id]");

            return 1;
        }

        if ($task->trashed()) {
            $task->restore();
            $this->info("Task restored [task=$task->id, project=$task->project_id]");
        }
        if ($task->status !== 'due') {
            $task->update(['status' => 'due']);
            $this->info("Task status updated to 'due' [task=$task->id, project=$task->project_id]");
        }

        $reportModel::create([
            'framework_key' => $task->project->framework_key,
            'task_id' => $task->id,
            "{$type}_id" => $entity->id,
            'status' => 'due',
            'due_at' => $task->due_at,
        ]);

        $this->info("Report created for $type $uuid");

        return 0;
    }
}

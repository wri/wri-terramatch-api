<?php

namespace App\Console\Commands;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicatedTasksCommand extends Command
{
    protected $signature = 'remove-duplicate-tasks {--due_at=} {--framework_key=}';

    protected $description = 'Process tasks by removing duplicates based on project_id and selecting earliest due_at';

    public function handle()
    {
        $specificDueAt = $this->option('due_at');
        $frameworkKey = $this->option('framework_key');

        if (! $specificDueAt || ! $frameworkKey) {
            $this->error('The --due_at and --framework_key options are required.');
            exit(1);
        }

        $duplicateTaskIds = DB::table("v2_tasks as t1")
            ->select('t1.id')
            ->join('v2_projects','t1.project_id', '=', 'v2_projects.id')
            ->join("v2_tasks as t2", function ($join) use ($specificDueAt) {
                $join->on("t1.project_id", '=', "t2.project_id")
                    ->on('t1.due_at', '=', 't2.due_at')
                    ->whereRaw('t1.id > t2.id')
                    ->where('t1.due_at', '=', $specificDueAt);
            })
            ->where('v2_projects.framework_key', $frameworkKey)
            ->pluck('t1.id');
        $duplicateTasks = Task::whereIn('id', $duplicateTaskIds)->get();

        foreach ($duplicateTasks as $task) {
            /** @var Task $task */
            $task->projectReport()->delete();
            $task->siteReports()->delete();
            $task->nurseryReports()->delete();
            $task->delete();
        }

        $this->info('Duplicate tasks and reports with framework_key "' . $frameworkKey . '" and due_at "' . $specificDueAt . '" removed successfully.');

        $this->showDeletedTasks($duplicateTasks);

        $this->info('Tasks processed successfully.');
    }

    protected function showDeletedTasks($duplicateTasks): void
    {
        $headers = ['ID', 'Project ID', 'Due At'];
        $taskRows = [];

        foreach ($duplicateTasks as $task) {
            $taskRows[] = [$task->id, $task->project_id, $task->due_at];
        }

        $this->info('Deleted tasks:');
        $this->table($headers, $taskRows);
    }
}

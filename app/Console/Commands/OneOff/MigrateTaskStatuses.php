<?php

namespace App\Console\Commands\OneOff;

use App\Exceptions\InvalidStatusException;
use App\Models\V2\Tasks\Task;
use App\StateMachines\ReportStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Console\Command;

class MigrateTaskStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-task-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all Tasks in database to match current status state machine values';

    private const VALID_STATUSES = [
        TaskStatusStateMachine::DUE,
        TaskStatusStateMachine::AWAITING_APPROVAL,
        TaskStatusStateMachine::NEEDS_MORE_INFORMATION,
        TaskStatusStateMachine::APPROVED,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $numClean = 0;
        $numErrors = 0;
        // Since we want to operate on trashed models too and we need to do a direct DB update (because the current
        // statuses aren't part of the updated state machine), we're re-implementing most of the logic in
        // Task->checkStatus
        Task::withoutTimestamps(function () use (&$numErrors, &$numClean) {
            Task::withTrashed()->whereNotIn('status',self::VALID_STATUSES)->chunkbyId(
                100,
                function ($tasks) use (&$numErrors, &$numClean) {
                    foreach ($tasks as $task) {
                        try {
                            // Fake the status into 'awaiting-approval' so that checkStatus()'s transitions are all
                            // legal.
                            $task->status = TaskStatusStateMachine::AWAITING_APPROVAL;
                            $task->checkStatus();
                            if ($task->status == TaskStatusStateMachine::AWAITING_APPROVAL) {
                                // if we "transitioned" to awaiting approval, the task won't have been saved because
                                // that's what we faked into above
                                $task->save();
                            }

                            $numClean++;
                        } catch (InvalidStatusException $exception) {
                            if ($this->processException($task, $exception)) {
                                $numClean++;
                            } else {
                                $numErrors++;
                            }
                        }
                    }
                });
        });

        echo "Migration completed. [Tasks with errors: $numErrors, successful transitions: $numClean]";
    }

    private function processException(Task $task, InvalidStatusException $exception): bool
    {
        if (!$task->projectReport()->exists() && !$task->siteReports()->exists() && !$task->nurseryReports()->exists()) {
            echo "Task $task->id was due on $task->due_at and has no associated reports. Moving to 'approved'.\n";
            $task->status = TaskStatusStateMachine::APPROVED;
            $task->save();
            return true;
        }

        $reportRelations = collect([$task->projectReport(), $task->siteReports(), $task->nurseryReports()]);
        $reportStatuses = $reportRelations->map(function ($relation) {
            return $relation->distinct()->pluck('status')->all();
        })->flatten()->unique();
        if (
            $reportStatuses
            ->intersect([ReportStatusStateMachine::DUE, ReportStatusStateMachine::STARTED])
            ->isNotEmpty()
        ) {
            echo "Task $task->id was due on $task->due_at and has reports in 'due' or 'started'. Moving to 'due'.\n";
            $task->status = TaskStatusStateMachine::DUE;
            $task->save();
            return true;
        }

        $message = $exception->getMessage();
        echo "Task $task->id: $message\n";
        return false;
    }
}

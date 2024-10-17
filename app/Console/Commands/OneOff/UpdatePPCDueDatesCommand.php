<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdatePPCDueDatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update:ppc-due-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update due dates shown on PPC reports.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('update ppc due dates');

        $tasks = Task::whereHas('project', function ($query) {
            $query->where('framework_key', 'ppc');
        })->get();
        foreach ($tasks as $task) {
            $project = $task->project;
            if ($project && $project->framework_key == 'ppc') {
                $task->due_at = Carbon::parse($task->due_at)->setTimezone('UTC')->setHour(5);
                $task->save();
            }

            $reports = collect([$task->projectReport])->concat($task->siteReports)->concat($task->nurseryReports)->filter(function ($report) {
                return $report !== null;
            });

            foreach ($reports as $report) {
                if ($report->framework_key !== 'ppc') {
                    continue;
                }

                $report->due_at = Carbon::parse($report->due_at)->setTimezone('UTC')->setHour(5);
                $report->save();
            }
        }

        $this->info('All due dates updated successfully.');

        return 0;
    }
}

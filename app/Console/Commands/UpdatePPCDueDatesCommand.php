<?php

namespace App\Console\Commands;

use App\Models\V2\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdatePPCDueDatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:ppc-due-dates';

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
        $datesToUpdate = [
            '2022-12' => '2023-01-06 04:00:00.000',
            '2023-01' => '2023-02-03 04:00:00.000',
            '2023-02' => '2023-03-03 04:00:00.000',
            '2023-03' => '2023-04-07 04:00:00.000',
            // Q2 (April-June) 2024
            '2024-04' => '2024-07-05 04:00:00.000',
            '2024-05' => '2024-07-05 04:00:00.000',
            '2024-06' => '2024-07-05 04:00:00.000',
            // Q3 (July-Sept) 2024
            '2024-07' => '2024-10-04 04:00:00.000',
            '2024-08' => '2024-10-04 04:00:00.000',
            '2024-09' => '2024-10-04 04:00:00.000',
        ];

        foreach ($datesToUpdate as $reportingPeriod => $correctDueDate) {
            list($year, $month) = explode('-', $reportingPeriod);
            $tasks = Task::where(function ($query) use ($reportingPeriod, $year, $month) {
                $query->where('period_key', $reportingPeriod)
                      ->orWhere('period_key', $year . '-' . intval($month));
            })->whereHas('project', function ($query) {
                $query->where('framework_key', 'ppc');
            })->get();
            foreach ($tasks as $task) {
                $project = $task->project;

                if ($project && $project->framework_key == 'ppc') {
                    $task->due_at = $correctDueDate;
                    $task->save();
                }

                $reports = collect($task->projectReport->get())->concat($task->siteReports)->concat($task->nurseryReports);

                foreach ($reports as $report) {
                    if ($report->framework_key !== 'ppc') {
                        continue;
                    }
                    $report->due_at = $correctDueDate;
                    $report->save();
                }
            }

        }

        $this->info('All due dates updated successfully.');

        return 0;
    }
}

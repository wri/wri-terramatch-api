<?php

namespace App\Console\Commands\Migration;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use Illuminate\Console\Command;

class CreateTasksMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:create-tasks {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate create tasks for legacy reports';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Task::truncate();
        }

        $projects = Project::all();

        foreach ($projects as $project) {
            $withDueResult = ProjectReport::selectRaw('YEAR(due_at) as year, MONTH(due_at) as month')
                ->where('project_id', $project->id)
                ->whereNotNull('due_at')
                ->groupBy('year', 'month')
                ->get()->toArray();

            foreach ($withDueResult as $item) {
                $report = ProjectReport::where('project_id', $project->id)
                    ->whereMonth('due_at', $item['month'])
                    ->whereYear('due_at', $item['year'])
                    ->first();

                $map = [
                        'organisation_id' => $project->organisation_id,
                        'project_id' => $project->id,
                        'status' => Task::STATUS_COMPLETE,
                        'period_key' => $item['year'] . '-' . ($item['month'] < 10 ? '0'. $item['month'] : $item['month']),
                        'due_at' => $report->due_at,
                ];

                Task::create($map);
            }

            $withoutDueResult = ProjectReport::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
                ->where('project_id', $project->id)
                ->whereNull('due_at')
                ->groupBy('year', 'month')
                ->get()->toArray();


            foreach ($withoutDueResult as $item) {
                $report = ProjectReport::where('project_id', $project->id)
                    ->whereMonth('created_at', $item['month'])
                    ->whereYear('created_at', $item['year'])
                    ->first();

                $map = [
                        'organisation_id' => $project->organisation_id,
                        'project_id' => $project->id,
                        'status' => Task::STATUS_COMPLETE,
                        'period_key' => $item['year'] . '-' . ($item['month'] < 10 ? '0' . $item['month'] : $item['month']),
                        'due_at' => $report->created_at,
                ];

                $exists = Task::where('organisation_id', $project->organisation_id)
                    ->where('project_id', $project->id)
                    ->where('period_key',  $item['year'] . '-' . ($item['month'] < 10 ? '0' . $item['month'] : $item['month']))
                    ->count();

                if ($exists == 0) {
                    Task::create($map);
                }
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }
}

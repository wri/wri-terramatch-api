<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Tasks\Task;
use App\Models\Framework;
use App\Models\V2\Action;

class UpdateDueDateForReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-due-date-for-reports {startCreateDate} {endCreateDate} {dueDatePPC} {dueDateTerrafund}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update due dates for reports';

    public function handle(): int
    {
   
        $startCreateDate = Carbon::parse($this->argument('startCreateDate'));
        $endCreateDate = Carbon::parse($this->argument('endCreateDate'));
        $dueDatePPC = Carbon::parse($this->argument('dueDatePPC'));
        $dueDateTerrafund = Carbon::parse($this->argument('dueDateTerrafund'));
        
        $allFrameworks = Framework::all();

        $frameworks = $allFrameworks->map(function ($framework) {
            return $framework->slug;
        })->toArray();


        foreach ($frameworks as $framework) {
            Project::where('framework_key', $framework)
            ->chunkById(100, function ($projects) use ($startCreateDate, $endCreateDate, $framework, $dueDatePPC, $dueDateTerrafund) {
                foreach ($projects as $project) {
                    $finalDueDate = $framework === 'ppc' ? $dueDatePPC : $dueDateTerrafund;
                    ProjectReport::whereBetween('created_at', [$startCreateDate, $endCreateDate])
                    ->where('project_id', $project->id)
                    ->where('framework_key', $framework)
                    ->update(['due_at' => $finalDueDate]);

                    Task::whereBetween('created_at', [$startCreateDate, $endCreateDate])
                    ->where('project_id', $project->id)
                    ->update(['due_at' => $finalDueDate, 'period_key' => $finalDueDate->format('Y-n')]);

                    $nurseryCount = $project->nurseries()->count();
                    $siteCount = $project-> sites()->count();
                    if ($nurseryCount != 0 && $siteCount != 0) {
                        $message = 'Project, site and nursery reports available';
                    } elseif ($nurseryCount > 0) {
                        $message = 'Project and nursery reports available';
                    } elseif ($siteCount > 0) {
                        $message = 'Project and site reports available';
                    } else {
                        $message = 'Project report available';
                    }

                    Action::whereBetween('created_at', [$startCreateDate, $endCreateDate])
                    ->where('project_id', $project->id)
                    ->update(['text' => $message]);

                }
            });

        }
        $this->info('Due dates updated successfully.');
        return 0;
    }
}

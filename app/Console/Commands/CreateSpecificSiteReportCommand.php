<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateSpecificSiteReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-specific-site-report {uuid} {framework_key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a site report for a specific site';

    public function handle(): int
    {
        $uuid = $this->argument('uuid');
        $frameworkKey = $this->argument('framework_key');

        $site = Site::where('uuid', $uuid)
            ->where('framework_key', $frameworkKey)
            ->first();

        if ($site) {

            $task = Task::withTrashed()->where('project_id', $site->project_id)->latest()->first();
            $dueDate = Carbon::parse($task->due_at);

            if ($task) {
                if ($task->trashed()) {
                    $task->restore();
                    $this->info("Task restored for project {$site->project_id}");
                }
                if ($task->status !== 'due') {
                    $task->update(['status' => 'due']);
                    $this->info("Task status updated to 'due' for project {$site->project_id}");
                }
            } else {
                $this->error("Task not found for project {$site->project_id}");
            }
            $dueDate = Carbon::parse($dueDate);

            SiteReport::create([
                'framework_key' => $frameworkKey,
                'site_id' => $site->id,
                'status' => SiteReport::STATUS_DUE,
                'due_at' => $dueDate,
            ]);

            $this->info("Site report created for site $uuid with framework key $frameworkKey");
        } else {
            $this->error("Site with UUID $uuid and framework key $frameworkKey not found");
        }

        return 0;
    }
}
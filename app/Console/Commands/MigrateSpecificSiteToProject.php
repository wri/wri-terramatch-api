<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Tasks\Task;
use App\StateMachines\TaskStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MigrateSpecificSiteToProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:site-to-project 
                            {site_uuid : UUID of the site to migrate}
                            {destination_project_uuid : UUID of the destination project}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate a specific site and its reports to a new project, creating/updating tasks as needed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $siteUuid = $this->argument('site_uuid');
        $destinationProjectUuid = $this->argument('destination_project_uuid');

        $this->info("Starting migration of site '{$siteUuid}' to project '{$destinationProjectUuid}'...");

        // Find the site
        $site = Site::where('uuid', $siteUuid)->first();
        if (! $site) {
            $this->error("Site with UUID '{$siteUuid}' not found.");

            return Command::FAILURE;
        }

        // Find the destination project
        $destinationProject = Project::where('uuid', $destinationProjectUuid)->first();
        if (! $destinationProject) {
            $this->error("Destination project with UUID '{$destinationProjectUuid}' not found.");

            return Command::FAILURE;
        }

        $this->info("Found site: {$site->name} (UUID: {$site->uuid})");
        $this->info("Found destination project: {$destinationProject->name} (UUID: {$destinationProject->uuid})");

        // Get current project info
        $currentProject = $site->project()->first();
        $this->info("Current project: {$currentProject->name}");

        // Migrate the site
        $this->migrateSite($site, $destinationProject);

        // Migrate reports and handle tasks
        $this->migrateReportsAndTasks($site, $destinationProject);

        $this->info('Migration completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Migrate the site to the new project.
     */
    private function migrateSite(Site $site, Project $destinationProject): void
    {
        $oldProject = $site->project()->first();
        $site->project()->associate($destinationProject);
        $site->save();

        $this->info("✓ Site '{$site->name}' moved from '{$oldProject->name}' to '{$destinationProject->name}'");
    }

    /**
     * Migrate reports and handle task creation/updates.
     */
    private function migrateReportsAndTasks(Site $site, Project $destinationProject): void
    {
        // Get all reports for this site
        $reports = SiteReport::where('site_id', $site->id)->get();

        $this->info('Found ' . $reports->count() . ' reports to migrate');

        foreach ($reports as $report) {
            $this->handleReportMigration($report, $destinationProject);
        }
    }

    /**
     * Handle migration of a single report and its task.
     */
    private function handleReportMigration(SiteReport $report, Project $destinationProject): void
    {
        $this->info("Processing report: {$report->title} (Due: {$report->due_at})");

        // Look for existing task with the same due date
        $existingTask = Task::where('project_id', $destinationProject->id)
            ->where('due_at', $report->due_at)
            ->first();

        if ($existingTask) {
            // Task exists, update the report's task_id
            $report->task_id = $existingTask->id;
            $report->save();

            $this->info("  ✓ Updated report to use existing task: {$existingTask->id}");
        } else {
            // Task doesn't exist, create a new one
            $dueAt = Carbon::parse($report->due_at);
            $newTask = Task::create([
                'organisation_id' => $destinationProject->organisation_id,
                'project_id' => $destinationProject->id,
                'status' => TaskStatusStateMachine::DUE,
                'period_key' => $dueAt->year . '-' . $dueAt->month,
                'due_at' => $dueAt,
            ]);

            // Update the report's task_id
            $report->task_id = $newTask->id;
            $report->save();

            $this->info("  ✓ Created new task (ID: {$newTask->id}) and linked to report");
        }
    }
}

<?php

namespace App\Console\Commands\Migration;

use App\Models\DueSubmission;
use App\Models\Programme;
use App\Models\Site as PPCSite;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\V2\Action;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Console\Command;

class CreateDueReportsMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:create-due-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate create reports tasks and actions for legacy due reports';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));

        $this->handlePPCStubs();

        $this->handleTerrafundStubs();

        $this->actionsCleanup();
        echo('- - - Finished - - - ' . chr(10));
    }

    private function handleTerrafundStubs()
    {
        TerrafundDueSubmission::where('is_submitted', 0)->chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $stub) {
                switch($stub->terrafund_due_submissionable_type) {
                    case TerrafundProgramme::class:
                        $project = Project::where('old_model',  TerrafundProgramme::class)
                            ->where('old_id', $stub->terrafund_due_submissionable_id)
                            ->first();

                        if (! empty($project)) {
                            $report = ProjectReport::updateOrCreate([
                                'framework_key' => 'terrafund',
                                'project_id' => $project->id,
                                'status' => ReportStatusStateMachine::DUE,
                                'due_at' => $stub->due_at,
                            ], []);

                            $this->handleTask($project, $stub);
                            $this->handleActions($project, $report);
                        }

                        break;
                    case TerrafundSite::class:
                        $site = Site::where('old_model',  TerrafundSite::class)
                            ->where('old_id', $stub->terrafund_due_submissionable_id)
                            ->first();

                        if (! empty($site) && ! empty($site->project)) {
                            $report = SiteReport::updateOrCreate([
                                'framework_key' => 'terrafund',
                                'site_id' => $site->id,
                                'status' => ReportStatusStateMachine::DUE,
                                'due_at' => $stub->due_at,
                            ], []);

                            $this->handleTask($site->project, $stub);
                            $this->handleParentActions($report);
                        }

                        break;
                    case TerrafundNursery::class:
                        $nursery = Nursery::where('old_model',  TerrafundNursery::class)
                            ->where('old_id', $stub->terrafund_due_submissionable_id)
                            ->first();

                        if (! empty($nursery) && ! empty($nursery->project)) {
                            $report = NurseryReport::updateOrCreate([
                                'framework_key' => 'terrafund',
                                'nursery_id' => $nursery->id,
                                'status' => ReportStatusStateMachine::DUE,
                                'due_at' => $stub->due_at,
                            ], []);

                            $this->handleTask($nursery->project, $stub);
                            $this->handleParentActions($report);
                        }

                        break;
                }
            }
        });
    }

    private function handlePPCStubs()
    {
        DueSubmission::where('is_submitted', 0)->chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $stub) {
                switch($stub->due_submissionable_type) {
                    case Programme::class:
                        $project = Project::where('old_model',  Programme::class)
                            ->where('old_id', $stub->due_submissionable_id)
                            ->first();

                        if (! empty($project)) {
                            $report = ProjectReport::updateOrCreate([
                                'framework_key' => 'ppc',
                                'project_id' => $project->id,
                                'status' => ReportStatusStateMachine::DUE,
                                'due_at' => $stub->due_at,
                            ], []);

                            $this->handleTask($project, $stub);
                            $this->handleActions($project, $report);
                        }

                        break;
                    case PPCSite::class:
                        $site = Site::where('old_model',  PPCSite::class)
                            ->where('old_id', $stub->due_submissionable_id)
                            ->first();

                        if (! empty($site) && ! empty($site->project)) {
                            $report = SiteReport::updateOrCreate([
                                'framework_key' => 'ppc',
                                'site_id' => $site->id,
                                'status' => ReportStatusStateMachine::DUE,
                                'due_at' => $stub->due_at,
                            ], []);

                            $this->handleTask($site->project, $stub);
                            $this->handleParentActions($report);
                        }

                        break;
                }
            }
        });
    }

    private function handleActions($project, $report)
    {
        $nurseryCount = $project->nurseries()->count();
        $siteCount = $project->sites()->count();

        if ($nurseryCount != 0 && $siteCount != 0) {
            $message = 'Project, site and nursery reports available';
        } elseif ($nurseryCount > 0) {
            $message = 'Project and nursery reports available';
        } elseif ($siteCount > 0) {
            $message = 'Project and site reports available';
        } else {
            $message = 'Project report available';
        }

        Action::updateOrCreate([
            'status' => Action::STATUS_PENDING,
            'targetable_type' => ProjectReport::class,
            'targetable_id' => $report->id,
            'type' => Action::TYPE_NOTIFICATION,
            'title' => 'Project report',
            'sub_title' => '',
            'text' => $message,
            'project_id' => $project->id,
            'organisation_id' => data_get($project->organisation, 'id'),
        ], []);
    }

    private function handleTask($project, $stub)
    {
        Task::updateOrCreate(
            [
                'organisation_id' => $project->organisation_id,
                'project_id' => $project->id,
                'period_key' => $stub->due_at->year . '-' . ($stub->due_at->month < 10 ? '0'. $stub->due_at->month : $stub->due_at->month),
            ],
            [
                'status' => Task::STATUS_DUE,
                'due_at' => $stub->due_at,
            ]
        );
    }

    private function handleParentActions($item)
    {
        $project = $item->project;

        if (empty($project)) {
            return;
        }

        $month = $item->due_at->month;
        $year = $item->due_at->year;

        $report = ProjectReport::where('project_id', $project->id)
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->first();

        if (empty($report)) {
            $report = ProjectReport::create([
                'framework_key' => $project->faramework_key,
                'project_id' => $project->id,
                'status' => ReportStatusStateMachine::DUE,
                'due_at' => $item->due_at,
            ]);
        }

        $action = Action::where('targetable_type', ProjectReport::class)
            ->where('targetable_id', $report->id)
            ->where('type', Action::TYPE_NOTIFICATION)
            ->first();

        if (empty($action)) {
            $nurseryCount = $project->sites()->count();
            $siteCount = $project->nurseries()->count();

            if ($nurseryCount != 0 && $siteCount != 0) {
                $message = 'Project, site and nursery reports available';
            } elseif ($nurseryCount > 0) {
                $message = 'Project and nursery reports available';
            } elseif ($siteCount > 0) {
                $message = 'Project and site reports available';
            } else {
                $message = 'Project report available';
            }

            Action::create([
                'status' => Action::STATUS_PENDING,
                'targetable_type' => ProjectReport::class,
                'targetable_id' => $report->id,
                'type' => Action::TYPE_NOTIFICATION,
                'title' => 'Project report',
                'sub_title' => '',
                'text' => $message,
                'project_id' => $project->id,
                'organisation_id' => data_get($project->organisation, 'id'),
            ]);
        }
    }

    private function actionsCleanup()
    {
        $actions = Action::whereNull('project_id')->get();

        foreach ($actions as $action) {
            if (data_get($actions, 'targetable_type') == Project::class) {
                $action->update(['project_id' => $action->targetable_type]);

                continue;
            }

            if (data_get($actions, 'targetable_type') == UpdateRequest::class) {
                $entity = $action->updaterequestable;
            } else {
                $entity = $action->targetable;
            }

            if (empty($entity)) {
                continue;
            }

            if (! empty($entity->project)) {
                $action->update(['project_id' => $entity->project->id]);
            }
        }
    }
}

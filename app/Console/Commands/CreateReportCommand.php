<?php

namespace App\Console\Commands;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\FinancialReport;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CreateReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-report {uuid} {--T|type=} {--D|due_at=} {--A|all_reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a report for a specific project, site or nursery';

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

            case 'financial':
                $entityModel = Organisation::class;
                $reportModel = FinancialReport::class;

                break;

            default:
                $this->error('Type must be one of "site", "nursery", "project", or "financial"');

                return 1;
        }

        $uuid = $this->argument('uuid');

        $entity = $entityModel::where('uuid', $uuid)->first();
        if ($entity == null) {
            $this->error("Entity.php not found [type=$type, uuid=$uuid]");

            return 1;
        }

        if ($type === 'project') {
            if (empty($this->option('due_at'))) {
                $this->error('--due_at is required for project report generation');

                return 1;
            }

            $dueAt = Carbon::parse($this->option('due_at'));
            $task = Task::create([
                'organisation_id' => $entity->organisation_id,
                'project_id' => $entity->id,
                'status' => TaskStatusStateMachine::DUE,
                'period_key' => $dueAt->year . '-' . $dueAt->month,
                'due_at' => $dueAt,
            ]);
        } elseif ($type === 'financial') {
            // For financial reports, no task is required by default, but you can link if needed
            $dueAtOption = $this->option('due_at');
            $dueAt = !empty($dueAtOption) ? Carbon::parse($dueAtOption) : null;
            $yearOfReport = $dueAt?->year ?? Carbon::now()->year;
            $report = $reportModel::create([
                'organisation_id' => $entity->id,
                'status' => 'due',
                'year_of_report' => $yearOfReport,
                'currency' => $entity?->currency,
                'fin_start_month' => $entity?->fin_start_month,
                'update_request_status' => 'no-update',
                'due_at' => $dueAt,
            ]);
            $this->info("Financial report created for organisation $uuid");

            FinancialIndicators::where('organisation_id', $entity->id)
                ->whereNull('financial_report_id')
                ->get()
                ->each(function ($indicator) use ($entity, $report) {
                    $newIndicator = FinancialIndicators::create([
                        'organisation_id' => $entity->id,
                        'year' => $indicator->year,
                        'collection' => $indicator->collection,
                        'amount' => $indicator->amount,
                        'description' => $indicator->description,
                        'financial_report_id' => $report->id,
                    ]);

                    if ($indicator->collection === FinancialIndicators::COLLECTION_NOT_COLLECTION_DOCUMENTS) {
                        $mediaItems = $indicator->getMedia('documentation');
                        foreach ($mediaItems as $media) {
                            $newMedia = $media->replicate();
                            $newMedia->model_id = $newIndicator->id;
                            $newMedia->uuid = (string) Str::uuid();
                            $newMedia->save();
                        }
                    }
                });

            return 0;
        } else {
            $task = Task::withTrashed()->where('project_id', $entity->project_id)->latest()->first();
        }

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

        if ($type == 'project' && $this->option('all_reports')) {
            foreach ($entity->sites as $site) {
                Artisan::call('create-report -Tsite ' . $site->uuid);
            }

            foreach ($entity->nurseries as $nursery) {
                Artisan::call('create-report -Tnursery ' . $nursery->uuid);
            }
        }

        $this->info("Report created for $type $uuid");

        return 0;
    }
}

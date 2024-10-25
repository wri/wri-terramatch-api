<?php

namespace App\Console\Commands;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GenerateReportingTaskToHbfEnterprisesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'reports:generate {--period=}';
    protected $description = 'Generates HBF and TerraFund reports based on the reporting period';

    public function handle()
    {
        $today = Carbon::now();
        $yearNow = $today->year;
        $hbfPeriods = [
            //May-October
            '11-01' => ['05', '06', '07', '08', '09', '10'],
            //November-April
            '05-01' => ['11', '12', '01', '02', '03', '04'],
        ];

        $terraFundEnterprisesPeriods = [
            //January-June
            '01-06',
            //July-December
            '07-12',
        ];
        
        $hbfDateGenerate = [
            $yearNow.'-11-01' => $yearNow.'-12-01 05:00:00',
            $yearNow.'-05-01' => $yearNow.'-06-01 05:00:00',
        ];

        $terraFundEnterprisesDateGenerate = [
            $yearNow.'-07-01' => $yearNow.'-07-30 05:00:00',
            $yearNow.'-01-01' => $yearNow.'-01-30 05:00:00',
        ];

        // Lógica para HBF
        $dateToday = $today->format('Y-m-d');
        Log::info('Date today');
        Log::info($dateToday);
        Log::info('Year now');
        Log::info($yearNow.'-11-01');
        Log::info('Comparation');
        Log::info($yearNow.'-11-01' == $dateToday ? 'true' : 'false');

        // if ($yearNow.'-11-01' == $dateToday || $yearNow.'-05-01' == $dateToday) {
        if (true) {
            $projectsHbf = Project::where('framework_key', 'hbf')->get();
            // $due_at = $hbfDateGenerate[$dateToday];
            foreach ($projectsHbf as $entity) {
                $tasks = Task::where('project_id', $entity->id)->get();
                $projectReports = $entity->reports;
                $sites = $entity->sites;
                $nurseries = $entity->nurseries;
                // if ($yearNow.'-11-01' == $dateToday) {
                if (true) {
                    foreach ($hbfPeriods as $key => $hbfPeriods) {
                        if ($key == '11-01') {
                            foreach($hbfPeriods as $period) {
                                $periodTask = $tasks->where('period_key', $yearNow . '-' . $period)->first();
                                if (!$periodTask) {
                                    $task = Task::create([
                                        'organisation_id' => $entity->organisation_id,
                                        'project_id' => $entity->id,
                                        'status' => TaskStatusStateMachine::DUE,
                                        'period_key' => $yearNow . '-' . $period,
                                        'due_at' => $yearNow.'-12-01 05:00:00',
                                    ]);
                                    // create project report to period
                                    $projectReportModel = ProjectReport::class;
                                    $this->createReportModel($projectReportModel, $entity, $task, 'project');
                                    // create site reports
                                    $siteReportModel = SiteReport::class;
                                    foreach ($sites as $site) {
                                        $this->createReportModel($siteReportModel, $site, $task, 'site');
                                    }
                                    // create nursery reports
                                    $nurseryReportModel = NurseryReport::class;
                                    foreach ($nurseries as $nursery) {
                                        $this->createReportModel($nurseryReportModel, $nursery, $task, 'nursery');
                                    }
                                } else {
                                    // create site reports
                                    $siteReportModel = SiteReport::class;
                                    foreach ($sites as $site) {
                                        if ($site->reports->where('task_id', $periodTask->id)->count() == 0) {
                                            $this->createReportModel($siteReportModel, $site, $periodTask, 'site');
                                        }
                                    }
                                    // create nursery reports
                                    $nurseryReportModel = NurseryReport::class;
                                    foreach ($nurseries as $nursery) {
                                        if ($site->reports->where('task_id', $periodTask->id)->count() == 0) {
                                            $this->createReportModel($siteReportModel, $site, $periodTask, 'nursery');
                                        }
                                    }
                                }
                            }

                        }
                    }
                    // $hbfPeriods = $hbfPeriods['11-01'];
                    // foreach($hbfPeriods[0] as $period) {
                    //     // $task = Task::create([
                    //     //     'organisation_id' => $entity->organisation_id,
                    //     //     'project_id' => $entity->id,
                    //     //     'status' => TaskStatusStateMachine::DUE,
                    //     //     'period_key' => $yearNow . '-' . $period,
                    //     //     'due_at' => $due_at,
                    //     // ]);
                    // }
                    // foreach ($entity->sites as $site) {
                    //     // Artisan::call('create-report -Tsite ' . $site->uuid);
                    // }
        
                    // foreach ($entity->nurseries as $nursery) {
                    //     // Artisan::call('create-report -Tnursery ' . $nursery->uuid);
                    // }
                    //Generate all reports for HBF
                    // Lógica    periodo de mayo-octubre
                    $this->generateReport('HBF', 'May-October');
                } elseif ($yearNow.'-05-01' == $dateToday)  {
                    
                    // foreach ($entity->sites as $site) {
                    //     Artisan::call('create-report -Tsite ' . $site->uuid);
                    // }
        
                    // foreach ($entity->nurseries as $nursery) {
                    //     Artisan::call('create-report -Tnursery ' . $nursery->uuid);
                    // }
                    // // Lógica para periodo de noviembre-abril
                    // $this->generateReport('HBF', 'November-April');
                }
            }
        }

        // Lógica para TerraFund
        elseif ($yearNow.'-07-01' == $dateToday || $yearNow.'-01-01' == $dateToday) {
            // $projectsTerraFund = Project::where('framework_key', 'enterprises')->get();
            // if ($today->between(Carbon::create($today->year, 1, 1), Carbon::create($today->year, 6, 30))) {
            //     // Lógica para periodo de enero-junio
            //     $this->generateReport('TerraFund', 'January-June');
            // } else {
            //     // Lógica para periodo de julio-diciembre
            //     $this->generateReport('TerraFund', 'July-December');
            // }
        } else {
            $this->error('Please specify a valid reporting period.');
        }

        $this->info('Reports generated successfully.');
    }

    protected function generateReport($type, $period)
    {
        // Aquí iría la lógica de generación del reporte
        $this->info("Generating $type report for the $period period.");
    }

    protected function createReportModel($reportModel, $entity, $task, $type)
    {
        $reportModel::create([
            'framework_key' => $task->project->framework_key,
            'task_id' => $task->id,
            "{$type}_id" => $entity->id,
            'status' => 'due',
            'due_at' => $task->due_at,
        ]);
    }

    protected function createTask($entity, $period, $yearNow)
    {
        $task = Task::create([
            'organisation_id' => $entity->organisation_id,
            'project_id' => $entity->id,
            'status' => TaskStatusStateMachine::DUE,
            'period_key' => $yearNow . '-' . $period,
            'due_at' => $yearNow.'-12-01 05:00:00',
        ]);
    }

    protected function createProjectReport($entity, $task)
    {
        $projectReportModel = ProjectReport::class;
        $this->createReportModel($projectReportModel, $entity, $task, 'project');
    }

    protected function createSiteReports($site, $task)
    {
        $siteReportModel = SiteReport::class;
        $this->createReportModel($siteReportModel, $site, $task, 'site');
    }

    protected function createNurseryReports($site, $task)
    {
        $siteReportModel = SiteReport::class;
        $this->createReportModel($siteReportModel, $site, $task, 'nursery');
    }
}

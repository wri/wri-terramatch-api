<?php

namespace App\Console\Commands;

use App\Helpers\TerrafundDashboardQueryHelper;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ExportApprovedDashboardDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-approved-dashboard-data-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'return CSV for approved projects';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $request = new Request(['filter' => []]);
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->with(['organisation:id,type,name'])
            ->select([
                'v2_projects.uuid',
                'v2_projects.id',
            ])
            ->get();
        $csvFile = fopen('projects_report.csv', 'w');
        fputcsv($csvFile, ['Project UUID', 'Trees Planted to Date', 'Hectares Under Restoration', 'Jobs Created']);

        foreach ($projects as $project) {
            fputcsv($csvFile, [
                $project->uuid,
                $project->approved_trees_planted_count,
                $project->total_hectares_restored_sum,
                $project->total_approved_jobs_created,
            ]);
        }
        fclose($csvFile);

        echo "CSV file 'projects_report.csv' created successfully.";
    }
}

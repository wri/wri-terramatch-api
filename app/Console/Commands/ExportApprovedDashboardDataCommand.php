<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\TerrafundDashboardQueryHelper;
use App\Services\Dashboard\RunTotalHeaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

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
                'v2_projects.total_hectares_restored_goal',
                'v2_projects.trees_grown_goal',
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

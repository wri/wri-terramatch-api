<?php

namespace Database\Seeders;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class UpdateProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $filePath = __DIR__ . '/../../resources/seeds/bulk_approve_projects.csv';

        if (! File::exists($filePath)) {
            $this->command->error("CSV file not found at {$filePath}");

            return;
        }

        $data = array_map('str_getcsv', file($filePath));
        $header = array_shift($data);
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, count($data));
        $progressBar->setFormat('Processing: %current% [%bar%] %percent:3s%%');

        $progressBar->start();

        foreach ($data as $row) {
            $uuid = $row[0];

            $project = Project::where('uuid', $uuid)->first();

            if ($project) {
                ProjectReport::where('project_id', $project->id)
                    ->whereIn('status', ['awaiting-approval', 'needs-more-information'])
                    ->update(['status' => 'approved']);

                $sites = $project->sites;
                foreach ($sites as $site) {
                    SiteReport::where('site_id', $site->id)
                        ->whereIn('status', ['awaiting-approval', 'needs-more-information'])
                        ->update(['status' => 'approved']);
                }

                Task::where('project_id', $project->id)
                    ->whereIn('status', ['awaiting-approval', 'needs-more-information'])
                    ->update(['status' => 'approved']);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("\nUpdate complete!");
    }
}

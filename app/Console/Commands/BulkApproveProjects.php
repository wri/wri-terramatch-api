<?php

namespace App\Console\Commands;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class BulkApproveProjects extends Command
{
    protected $signature = 'bulk-approve-projects {file}';

    protected $description = 'Bulk approve projects from a CSV file';

    public function handle(): void
    {
        $filePath = $this->argument('file');

        if (! File::exists($filePath)) {
            $this->error("CSV file not found at {$filePath}");

            return;
        }

        $data = array_map('str_getcsv', file($filePath));
        $header = array_shift($data);
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, count($data));
        $progressBar->setFormat('Processing: %current% [%bar%] %percent:3s%%');

        $progressBar->start();


        $excludeDueDate = '2024-07-30';
        foreach ($data as $row) {
            $uuid = $row[0];

            $project = Project::where('uuid', $uuid)->first();

            if ($project) {
                ProjectReport::where('project_id', $project->id)
                    ->whereIn('status', ['awaiting-approval', 'needs-more-information'])
                    ->whereDate('due_at', '!=', $excludeDueDate)
                    ->update(['status' => 'approved']);

                $sites = $project->sites;
                foreach ($sites as $site) {
                    SiteReport::where('site_id', $site->id)
                        ->whereIn('status', ['awaiting-approval', 'needs-more-information'])
                        ->whereDate('due_at', '!=', $excludeDueDate)
                        ->update(['status' => 'approved']);
                }

                Task::where('project_id', $project->id)
                    ->whereIn('status', ['awaiting-approval', 'needs-more-information'])
                    ->whereDate('due_at', '!=', $excludeDueDate)
                    ->update(['status' => 'approved']);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("\nUpdate complete!");
    }
}

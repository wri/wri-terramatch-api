<?php

namespace Database\Seeders;

use App\Models\V2\Projects\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class InsertLandscapeData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $filePath = __DIR__ . '/../../resources/seeds/uuid-by-landscape.csv';

        if (! File::exists($filePath)) {
            $this->command->error("CSV file not found at {$filePath}");

            return;
        }

        $data = array_map('str_getcsv', file($filePath));
        $header = array_shift($data); // Remove headers

        $output = new ConsoleOutput();
        $createProgress = new ProgressBar($output, count($data));
        $updateProgress = new ProgressBar($output, count($data));

        $createdCount = 0;
        $updatedCount = 0;

        $createProgress->setFormat('Created: %current% [%bar%] %percent:3s%%');
        $updateProgress->setFormat('Updated: %current% [%bar%] %percent:3s%%');

        foreach ($data as $row) {
            $project = Project::updateOrCreate(
                ['uuid' => $row[0]],
                ['landscape' => $row[1]]
            );

            if ($project->wasRecentlyCreated) {
                $createdCount++;
                $createProgress->advance();
            } else {
                $updatedCount++;
                $updateProgress->advance();
            }
        }

        $createProgress->finish();
        $output->writeln('');
        $updateProgress->finish();

        $output->writeln("\nTotal Created: $createdCount");
        $output->writeln("Total Updated: $updatedCount");
    }
}

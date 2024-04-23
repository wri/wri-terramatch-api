<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use App\Models\V2\Projects\Project;

class AddNumericValuesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:lat-long';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add numeric values to lat and lon columns in v2 projects table';

    public function handle()
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            $project->update([
                'lat' => mt_rand(-90, 90) + mt_rand() / mt_getrandmax(),
                'long' => mt_rand(-180, 180) + mt_rand() / mt_getrandmax(),
            ]);
        }
        $this->info('Lat and long values updated successfully for all v2 projects.');
        return 0;
    }
}

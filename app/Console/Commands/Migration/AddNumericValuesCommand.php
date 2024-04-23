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
        $longMax = 60.53;
        $longMin = 29.32;
        $latMax = 75.16;
        $latMin = 38.49;
        $projects = Project::all();
        foreach ($projects as $project) {
            $project->update([
                'lat' => $this->generateRandomDecimal($latMin, $latMax),
                'long' => $this->generateRandomDecimal($longMin, $longMax),
            ]);
        };
        $this->info('Lat and long values updated successfully for all v2 projects.');
        return 0;
    }
    /**
     * Generate a random decimal number within a specified range.
     *
     * @param float $min Minimum value (inclusive)
     * @param float $max Maximum value (inclusive)
     * @return float Random decimal number within the specified range
     */
    private function generateRandomDecimal($min, $max)
    {
        $decimals = 2; // Number of decimal places
        $factor = pow(10, $decimals);
        $min *= $factor;
        $max *= $factor;
        $random = mt_rand($min, $max) / $factor;
        return $random;
    }
}

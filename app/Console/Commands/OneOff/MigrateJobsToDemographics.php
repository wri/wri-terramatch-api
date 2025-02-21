<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Console\Command;

class MigrateJobsToDemographics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-jobs-to-demographics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move jobs data to demographics';

    protected const JOBS_MAPPING = [
        'full-time' => [
            'gender' => [
                'male' => 'ft_men',
                'female' => 'ft_women',
                'non-binary' => 'ft_other'
            ],
            'age' => [
                'youth' => 'ft_youth',
                'non-youth' => 'ft_jobs_non_youth'
            ],
            'total' => 'ft_total'
        ],
        'part-time' => [
            'gender' => [
                'male' => 'pt_men',
                'female' => 'pt_women',
                'non-binary' => 'pt_other'
            ],
            'age' => [
                'youth' => 'pt_youth',
                'non-youth' => 'pt_non_youth'
            ],
            'total' => 'pt_total'
        ],
        'volunteer' => [
            'gender' => [
                'male' => 'volunteer_men',
                'female' => 'volunteer_women',
                'non-binary' => 'volunteer_other'
            ],
            'age' => [
                'youth' => 'volunteer_youth',
                'non-youth' => 'volunteer_non_youth'
            ],
            'caste' => [
                'marginalized' => 'volunteer_scstobc',
            ],
            'total' => 'volunteer_total'
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Moving project report jobs data to Demographics...");
        $this->withProgressBar(ProjectReport::count(), function ($progressBar) {
            ProjectReport::chunkById(100, function ($projectReports) use ($progressBar) {
                foreach ($projectReports as $projectReport) {
                    $this->convertJobs($projectReport);
                    $progressBar->advance();
                }
            });
        });

        $this->info("\n\nCompleted moving project report jobs data to Demographics.");
    }

    private function convertJobs(ProjectReport $projectReport): void
    {
        foreach (self::JOBS_MAPPING as $collection => $types) {
            /** @var Demographic $demographic */
            $demographic = null;
            foreach ($types as $type => $subtypes) {
                if ($type == "total") {
                    $field = $subtypes;
                    if ($demographic != null) {
                        $total = $demographic->entries()->gender()->sum('amount');
                        if ($projectReport[$field] > $total) {
                            // we've got a total that's greater than the sum of gender values, create a "unknown" gender
                            // row to fill the gap
                            $demographic->entries()->create([
                                'type' => 'gender',
                                'subtype' => 'unknown',
                                'amount' => $projectReport[$field] - $total
                            ]);
                        }
                    }
                } else {
                    // If none of the fields for this type exist, skip
                    $fields = collect(array_values($subtypes));
                    if ($fields->first(fn($field) => $projectReport[$field] > 0) == null) {
                        continue;
                    }

                    if ($demographic == null) {
                        $demographic = $projectReport->demographics()->create([
                            'type' => 'jobs',
                            'collection' => $collection,
                        ]);
                    }
                    foreach ($subtypes as $subtype => $field) {
                        $value = $projectReport[$field];
                        if ($value > 0) {
                            $demographic->entries()->create([
                                'type' => $type,
                                'subtype' => $subtype,
                                'amount' => $value
                            ]);
                        }
                    }
                }
            }
        }
    }
}

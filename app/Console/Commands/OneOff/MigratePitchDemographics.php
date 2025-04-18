<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\DemographicsMigration;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;

class MigratePitchDemographics extends Command
{
    use DemographicsMigration;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-pitch-demographics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move beneficiaries / jobs data in Project Pitch and Project to Demographics';

    protected const ALL_MAPPING = [
        'all' => [
            'percentage' => [
                'gender' => [
                    'male' => 'pct_beneficiaries_men',
                    'female' => 'pct_beneficiaries_women',
                ],
                'age' => [
                    'youth' => 'pct_beneficiaries_youth',
                ],
                'farmer' => [
                    'smallholder' => 'pct_beneficiaries_small',
                    'large-scale' => 'pct_beneficiaries_large',
                ],
                'caste' => [
                    'marginalized' => ['pct_beneficiaries_backward_class', 'pct_beneficiaries_marginalized'],
                ],
            ],
            'total' => ['proj_beneficiaries'],
        ],
    ];

    protected const EMPLOYEES_MAPPING = [
        'all' => [
            'percentage' => [
                'gender' => [
                    'male' => 'pct_employees_men',
                    'female' => 'pct_employees_women',
                ],
                'age' => [
                    'youth' => 'pct_employees_18to35',
                    'non-youth' => 'pct_employees_older35',
                ],
                'caste' => [
                    'marginalized' => 'pct_employees_marginalised',
                ],
            ],
            'total' => ['num_jobs_created', 'jobs_created_goal'],
        ],
    ];

    protected const MIGRATION_MAPPING = [
        'all-beneficiaries' => self::ALL_MAPPING,
        'employees' => self::EMPLOYEES_MAPPING,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Moving project pitch beneficiaries / jobs data to Demographics...');
        $this->withProgressBar(ProjectPitch::count(), function ($progressBar) {
            ProjectPitch::chunkById(100, function ($projectPitches) use ($progressBar) {
                foreach ($projectPitches as $projectPitch) {
                    $this->convertDemographics($projectPitch, self::MIGRATION_MAPPING);
                    $progressBar->advance();
                }
            });
        });
        $this->info("\n\nCompleted moving project pitch beneficiaries / jobs data to Demographics.");

        $this->info("\n\nMoving project beneficiaries / jobs data to Demographics...");
        $this->withProgressBar(Project::count(), function ($progressBar) {
            Project::chunkById(100, function ($projects) use ($progressBar) {
                foreach ($projects as $project) {
                    $this->convertDemographics($project, self::MIGRATION_MAPPING);
                    $progressBar->advance();
                }
            });
        });
        $this->info("\n\nCompleted moving project beneficiaries / jobs data to Demographics.");
    }
}

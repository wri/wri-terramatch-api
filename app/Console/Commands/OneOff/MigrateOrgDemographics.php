<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\DemographicsMigration;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;

class MigrateOrgDemographics extends Command
{
    use DemographicsMigration;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-org-demographics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move beneficiaries / jobs data in Organisations to Demographics';

    protected const ALL_MAPPING = [
        'all' => [
            'integer' => [
                'gender' => [
                    'male' => 'community_members_engaged_3yr_men',
                    'female' => 'community_members_engaged_3yr_women',
                ],
                'age' => [
                    'youth' => 'community_members_engaged_3yr_youth',
                    'non-youth' => 'community_members_engaged_3yr_non_youth',
                ],
                'farmer' => [
                    'smallholder' => 'community_members_engaged_3yr_smallholder',
                ],
                'caste' => [
                    'marginalized' => 'community_members_engaged_3yr_backward_class',
                ],
            ],
            'percent' => [
                'gender' => [
                    'male' => 'percent_engaged_men_3yr',
                    'female' => 'percent_engaged_women_3yr',
                ],
                'age' => [
                    'youth' => 'percent_engaged_under_35_3yr',
                    'non-youth' => 'percent_engaged_over_35_3yr',
                ],
                'farmer' => [
                    'smallholder' => 'percent_engaged_smallholder_3yr',
                ],
            ],
            'total' => ['total_engaged_community_members_3yr', 'community_members_engaged_3yr'],
        ],
    ];

    protected const EMPLOYEE_MAPPING = [
        'all' => [
            'integer' => [
                'gender' => [
                    'male' => 'male_employees',
                    'female' => 'female_employees',
                ],
                'age' => [
                    'youth' => 'young_employees',
                    'non-youth' => 'over_35_employees',
                ],
                'caste' => [
                    'marginalized' => 'num_of_marginalised_employees',
                ],
            ],
            'total' => ['total_employees', ['ft_permanent_employees', 'pt_permanent_employees', 'temp_employees']],
        ],
        'full-time' => [
            'total' => ['ft_permanent_employees'],
        ],
        'part-time' => [
            'total' => ['pt_permanent_employees'],
        ],
        'temp' => [
            'total' => ['temp_employees'],
        ],
    ];

    protected const MIGRATION_MAPPING = [
        'all-beneficiaries' => self::ALL_MAPPING,
        'employees' => self::EMPLOYEE_MAPPING,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Moving organisation beneficiaries / jobs data to Demographics...');
        $this->withProgressBar(Organisation::count(), function ($progressBar) {
            Organisation::chunkById(100, function ($organisations) use ($progressBar) {
                foreach ($organisations as $organisation) {
                    $this->convertDemographics($organisation, self::MIGRATION_MAPPING);
                    $progressBar->advance();
                }
            });
        });

        $this->info("\n\nCompleted moving organisation beneficiaries / jobs data to Demographics.");
    }
}

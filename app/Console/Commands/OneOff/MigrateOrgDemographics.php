<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;

class MigrateOrgDemographics extends Command
{
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
            'total' => ['total_employees', ['ft_permanent_employees', 'pt_permanent_employees']],
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
                    $this->convertDemographics($organisation);
                    $progressBar->advance();
                }
            });
        });

        $this->info("\n\nCompleted moving organisation beneficiaries / jobs data to Demographics.");
    }

    private function convertDemographics(Organisation $organisation): void
    {
        foreach (self::MIGRATION_MAPPING as $demographicType => $mapping) {
            foreach ($mapping as $collection => $fields) {
                /** @var Demographic $demographic */
                $demographic = null;

                $addDisaggregates = function ($disaggregateFields, $percentageTotal = null) use (&$demographic, $organisation, $demographicType, $collection) {
                    foreach ($disaggregateFields as $type => $subtypes) {
                        // If none of the fields for this type exist, skip
                        $fields = collect(array_values($subtypes));
                        if ($fields->first(fn ($field) => $organisation[$field] > 0) == null) {
                            continue;
                        }

                        if ($demographic == null) {
                            $demographic = $organisation->demographics()->create([
                                'type' => $demographicType,
                                'collection' => $collection,
                                'hidden' => false,
                            ]);
                        }
                        foreach ($subtypes as $subtype => $field) {
                            $value = $organisation[$field];
                            if ($value > 0) {
                                $existing = $demographic->entries()->where(['type' => $type, 'subtype' => $subtype])->first();
                                $reportedValue = $percentageTotal == null ? $value : round($percentageTotal * ($value / 100));
                                $finalValue = max($existing?->value ?? 0, $reportedValue);

                                if ($existing != null) {
                                    $existing->update(['value' => $finalValue]);
                                } else {
                                    $demographic->entries()->create(['type' => $type, 'subtype' => $subtype, 'amount' => $finalValue ]);
                                }
                            }
                        }
                    }
                };

                $calculateTotals = function () use (&$demographic, $organisation, $fields) {
                    $reportedTotal = 0;
                    foreach ($fields['total'] as $totalField) {
                        // Only use the current total field if we haven't already found a valid value.
                        if (! empty($reportedTotal)) {
                            break;
                        }

                        if (is_array($totalField)) {
                            // An array here indicates that the fields should be pulled and summed.
                            $reportedTotal = 0;
                            foreach ($totalField as $sumField) {
                                $reportedTotal += $organisation[$sumField] ?? 0;
                            }
                        } else {
                            $reportedTotal = $organisation[$totalField] ?? 0;
                        }
                    }

                    $genderTotal = $demographic?->entries()->gender()->sum('amount') ?? 0;
                    $ageTotal = $demographic?->entries()->age()->sum('amount') ?? 0;
                    return ['total' => max([$genderTotal, $ageTotal, $reportedTotal]), 'gender' => $genderTotal, 'age' => $ageTotal];
                };

                // First, add the disaggregates from integer reporting fields.
                $addDisaggregates($fields['integer']);

                if (! empty($fields['percentage'])) {
                    // Find the current reported total based on integer disaggregate values and reported total values
                    $reportedTotal = $calculateTotals()['total'];
                    // Now add the disaggregates from percentage based fields.
                    $addDisaggregates($fields['percentage'], $reportedTotal);
                }

                // Finally, calculate the final total and make sure the gender / age categories balance and match it
                $targetTotals = $calculateTotals();
                if ($targetTotals['total'] > 0) {
                    if ($demographic == null) {
                        $demographic = $organisation->demographics()->create([
                            'type' => $demographicType,
                            'collection' => $collection,
                            'hidden' => false,
                        ]);
                    }

                    if ($targetTotals['gender'] < $targetTotals['total']) {
                        $demographic->entries()->create([
                            'type' => 'gender',
                            'subtype' => 'unknown',
                            'amount' => $targetTotals['total'] - $targetTotals['gender'],
                        ]);
                    }
                    if ($targetTotals['age'] < $targetTotals['total']) {
                        $demographic->entries()->create([
                            'type' => 'age',
                            'subtype' => 'unknown',
                            'amount' => $targetTotals['total'] - $targetTotals['age'],
                        ]);
                    }
                }
            }
        }
    }
}

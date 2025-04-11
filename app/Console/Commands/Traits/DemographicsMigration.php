<?php

namespace App\Console\Commands\Traits;

use App\Models\V2\Demographics\Demographic;

trait DemographicsMigration
{
    protected function convertDemographics($model, $migrationMapping): void
    {
        foreach ($migrationMapping as $demographicType => $mapping) {
            foreach ($mapping as $collection => $fields) {
                /** @var Demographic $demographic */
                $demographic = null;

                $addDisaggregates = function ($disaggregateFields, $percentageTotal = null) use (&$demographic, $model, $demographicType, $collection) {
                    foreach ($disaggregateFields as $type => $subtypes) {
                        // If none of the fields for this type exist, skip
                        $fields = collect(array_values($subtypes));
                        if ($fields->first(fn ($field) => $model[$field] > 0) == null) {
                            continue;
                        }

                        if ($demographic == null) {
                            $demographic = $model->demographics()->create([
                                'type' => $demographicType,
                                'collection' => $collection,
                                'hidden' => false,
                            ]);
                        }

                        $percentageSum = 100;
                        if ($percentageTotal != null) {
                            $percentageSum = collect($subtypes)->values()->reduce(function ($percentageSum, $field) use ($model) {
                                return $percentageSum + $model[$field];
                            }, 0);
                        }

                        foreach ($subtypes as $subtype => $field) {
                            $value = $model[$field];
                            if ($value > 0) {
                                $existing = $demographic->entries()->where(['type' => $type, 'subtype' => $subtype])->first();
                                $reportedValue = $value;
                                if ($percentageTotal != null) {
                                    // Find how much this "percentage" takes up of the sum of percentages from this type. In
                                    // some cases, the data has a problem where for instance both male and female will be
                                    // specified as 100, which skews the final total. The final total is the most important
                                    // value, so to keep it stable, we sum the percentage totals within each entry type above
                                    // and then find what percentage this value is with respect to that total. This will get
                                    // the final total value to be close to the reported total value (there could be a rounding
                                    // error, but it should be no more than off by one).
                                    $value = $value / $percentageSum;
                                    $reportedValue = round($percentageTotal * $value);
                                }
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

                $calculateTotals = function () use (&$demographic, $model, $fields) {
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
                                $reportedTotal += $model[$sumField] ?? 0;
                            }
                        } else {
                            $reportedTotal = $model[$totalField] ?? 0;
                        }
                    }

                    $genderTotal = $demographic?->entries()->gender()->sum('amount') ?? 0;
                    $ageTotal = $demographic?->entries()->age()->sum('amount') ?? 0;

                    return ['total' => max([$genderTotal, $ageTotal, $reportedTotal]), 'gender' => $genderTotal, 'age' => $ageTotal];
                };

                // First, add the disaggregates from integer reporting fields.
                if (! empty($fields['integer'])) {
                    $addDisaggregates($fields['integer']);
                }

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
                        $demographic = $model->demographics()->create([
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

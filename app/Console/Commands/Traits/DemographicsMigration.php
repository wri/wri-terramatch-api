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
                        foreach ($subtypes as $subtype => $field) {
                            $value = $model[$field];
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

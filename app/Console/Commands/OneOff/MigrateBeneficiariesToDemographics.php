<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Console\Command;

class MigrateBeneficiariesToDemographics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-beneficiaries-to-demographics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move beneficiaries data to demographics';

    protected const ALL_MAPPING = [
        'all' => [
            'gender' => [
                'male' => 'beneficiaries_men',
                'female' => 'beneficiaries_women',
                'non-binary' => 'beneficiaries_other',
            ],
            'age' => [
                'youth' => 'beneficiaries_youth',
                'non-youth' => 'beneficiaries_non_youth',
            ],
            'farmer' => [
                'smallholder' => 'beneficiaries_smallholder',
                'large-scale' => 'beneficiaries_large_scale',
                'marginalized' => 'beneficiaries_scstobc_farmers',
            ],
            'caste' => [
                'marginalized' => 'beneficiaries_scstobc',
            ],
            'total' => 'beneficiaries',
        ],
    ];

    protected const TRAINING_MAPPING = [
        'training' => [
            'gender' => [
                'male' => 'beneficiaries_training_men',
                'female' => 'beneficiaries_training_women',
                'non-binary' => 'beneficiaries_training_other',
            ],
            'age' => [
                'youth' => 'beneficiaries_training_youth',
                'non-youth' => 'beneficiaries_training_non_youth',
            ],
            'total' => ['beneficiaries_skills_knowledge_increase', 'people_knowledge_skills_increased'],
        ],
    ];

    protected const MIGRATION_MAPPING = [
        'all-beneficiaries' => self::ALL_MAPPING,
        'training-beneficiaries' => self::TRAINING_MAPPING,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Moving project report beneficiaries data to Demographics...');
        $this->withProgressBar(ProjectReport::count(), function ($progressBar) {
            ProjectReport::chunkById(100, function ($projectReports) use ($progressBar) {
                foreach ($projectReports as $projectReport) {
                    $this->convertJobs($projectReport);
                    $progressBar->advance();
                }
            });
        });

        $this->info("\n\nCompleted moving project report beneficiaries data to Demographics.");
    }

    private function convertJobs(ProjectReport $projectReport): void
    {
        foreach (self::MIGRATION_MAPPING as $demographicType => $mapping) {
            foreach ($mapping as $collection => $types) {
                /** @var Demographic $demographic */
                $demographic = null;
                foreach ($types as $type => $subtypes) {
                    if ($type == 'total') {
                        $fields = is_array($subtypes) ? $subtypes : [$subtypes];
                        // Make sure gender / age demographics are balanced and reach at least to the "_total" field
                        // for this type of job from the original report. Pad gender and age demographics with an
                        // "unknown" if needed.
                        $genderTotal = $demographic?->entries()->gender()->sum('amount') ?? 0;
                        $ageTotal = $demographic?->entries()->age()->sum('amount') ?? 0;

                        $totals = [$genderTotal, $ageTotal];
                        foreach ($fields as $field) {
                            $totals[] = $projectReport[$field];
                        }
                        $targetTotal = max($totals);

                        if ($demographic == null && $targetTotal > 0) {
                            $demographic = $projectReport->demographics()->create([
                                'type' => $demographicType,
                                'collection' => $collection,
                                'hidden' => false,
                            ]);
                        }

                        if ($genderTotal < $targetTotal) {
                            $demographic->entries()->create([
                                'type' => 'gender',
                                'subtype' => 'unknown',
                                'amount' => $targetTotal - $genderTotal,
                            ]);
                        }
                        if ($ageTotal < $targetTotal) {
                            $demographic->entries()->create([
                                'type' => 'age',
                                'subtype' => 'unknown',
                                'amount' => $targetTotal - $ageTotal,
                            ]);
                        }
                    } else {
                        // If none of the fields for this type exist, skip
                        $fields = collect(array_values($subtypes));
                        if ($fields->first(fn ($field) => $projectReport[$field] > 0) == null) {
                            continue;
                        }

                        if ($demographic == null) {
                            $demographic = $projectReport->demographics()->create([
                                'type' => $demographicType,
                                'collection' => $collection,
                                'hidden' => false,
                            ]);
                        }
                        foreach ($subtypes as $subtype => $field) {
                            $value = $projectReport[$field];
                            if ($value > 0) {
                                $demographic->entries()->create([
                                    'type' => $type,
                                    'subtype' => $subtype,
                                    'amount' => $value,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        if ($projectReport->trainingBeneficiariesTotal > $projectReport->allBeneficiariesTotal) {
            // in this case, the training data had a greater gender total than the "all" gender total, so we want
            // to pad "all" so that they're equal.
            $padValue = $projectReport->trainingBeneficiariesTotal - $projectReport->allBeneficiariesTotal;

            $all = $projectReport->allBeneficiaries()->first();
            if ($all == null) {
                $all = $projectReport->demographics()->create([
                    'type' => Demographic::ALL_BENEFICIARIES_TYPE,
                    'collection' => 'all',
                    'hidden' => false,
                ]);
            }

            // We can assume that gender / age have already been balanced and just add the pad value to both
            $gender = $all->entries()->where(['type' => 'gender', 'subtype' => 'unknown'])->first();
            if ($gender == null) {
                $all->entries()->create([
                    'type' => 'gender',
                    'subtype' => 'unknown',
                    'amount' => $padValue,
                ]);
            } else {
                $gender->amount += $padValue;
                $gender->save();
            }

            $age = $all->entries()->where(['type' => 'age', 'subtype' => 'unknown'])->first();
            if ($age == null) {
                $all->entries()->create([
                    'type' => 'age',
                    'subtype' => 'unknown',
                    'amount' => $padValue,
                ]);
            } else {
                $age->amount += $padValue;
                $age->save();
            }
        }
    }
}

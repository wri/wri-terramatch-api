<?php

namespace App\Models\Traits;

use App\Exceptions\DemographicsException;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Demographics\DemographicEntry;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

trait HasDemographics
{
    public const DEMOGRAPHIC_TOTAL_ATTRIBUTES = [
        'workdaysPaid' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'paid'],
        'workdaysVolunteer' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'volunteer'],
        'workdaysDirectTotal' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'direct'],
        'workdaysConvergenceTotal' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'convergence'],
        'directRestorationPartners' => ['type' => Demographic::RESTORATION_PARTNER_TYPE, 'collections' => 'direct'],
        'indirectRestorationPartners' => ['type' => Demographic::RESTORATION_PARTNER_TYPE, 'collections' => 'indirect'],
        'jobsAllTotal' => ['type' => Demographic::JOBS_TYPE, 'collections' => 'all'],
        'jobsFullTimeTotal' => ['type' => Demographic::JOBS_TYPE, 'collections' => 'full-time'],
        'jobsPartTimeTotal' => ['type' => Demographic::JOBS_TYPE, 'collections' => 'part-time'],
        'employeesAllTotal' => ['type' => Demographic::EMPLOYEES_TYPE, 'collections' => 'all'],
        'employeesFullTimeTotal' => ['type' => Demographic::EMPLOYEES_TYPE, 'collections' => 'full-time'],
        'employeesPartTimeTotal' => ['type' => Demographic::EMPLOYEES_TYPE, 'collections' => 'part-time'],
        'employeesTempTotal' => ['type' => Demographic::EMPLOYEES_TYPE, 'collections' => 'temp'],
        'volunteersTotal' => ['type' => Demographic::VOLUNTEERS_TYPE],
        'allBeneficiariesTotal' => ['type' => Demographic::ALL_BENEFICIARIES_TYPE],
        'trainingBeneficiariesTotal' => ['type' => Demographic::TRAINING_BENEFICIARIES_TYPE],
        'indirectBeneficiariesTotal' => ['type' => Demographic::INDIRECT_BENEFICIARIES_TYPE],
    ];

    public const DEMOGRAPHIC_AGGREGATE_ATTRIBUTES = [
        'fullTimeJobsAggregate' => ['type' => Demographic::JOBS_TYPE, 'collection' => DemographicCollections::FULL_TIME],
        'fullTimeCltJobsAggregate' => ['type' => Demographic::JOBS_TYPE, 'collection' => DemographicCollections::FULL_TIME_CLT],
        'partTimeJobsAggregate' => ['type' => Demographic::JOBS_TYPE, 'collection' => DemographicCollections::PART_TIME],
        'partTimeCltJobsAggregate' => ['type' => Demographic::JOBS_TYPE, 'collection' => DemographicCollections::PART_TIME_CLT],
        'volunteersAggregate' => ['type' => Demographic::VOLUNTEERS_TYPE, 'collection' => DemographicCollections::VOLUNTEER],
        'allBeneficiariesAggregate' => ['type' => Demographic::ALL_BENEFICIARIES_TYPE, 'collection' => DemographicCollections::ALL],
        'indirectBeneficiariesAggregate' => ['type' => Demographic::INDIRECT_BENEFICIARIES_TYPE, 'collection' => DemographicCollections::INDIRECT],
        'allAssociatesAggregate' => ['type' => Demographic::ASSOCIATES_TYPE, 'collection' => DemographicCollections::ALL],
    ];

    public static function bootHasDemographics()
    {
        if (empty(static::DEMOGRAPHIC_COLLECTIONS)) {
            throw new InternalErrorException('No demographic collections defined');
        }

        collect(static::DEMOGRAPHIC_COLLECTIONS)->each(function ($collectionSets, $demographicType) {
            $attributePrefix = Str::camel($demographicType);
            self::resolveRelationUsing($attributePrefix, function ($entity) use ($demographicType) {
                return $entity->demographics()->type($demographicType);
            });

            $collections = match ($demographicType) {
                Demographic::WORKDAY_TYPE => collect([
                    data_get($collectionSets, 'paid', []),
                    data_get($collectionSets, 'volunteer', []),
                    data_get($collectionSets, 'finance', []),
                ])->flatten(),
                Demographic::RESTORATION_PARTNER_TYPE => collect([
                    data_get($collectionSets, 'direct', []),
                    data_get($collectionSets, 'indirect', []),
                ])->flatten(),
                Demographic::JOBS_TYPE => collect([
                    data_get($collectionSets, 'all', []),
                    data_get($collectionSets, 'full-time', []),
                    data_get($collectionSets, 'part-time', []),
                ])->flatten(),
                Demographic::EMPLOYEES_TYPE => collect([
                    data_get($collectionSets, 'all', []),
                    data_get($collectionSets, 'full-time', []),
                    data_get($collectionSets, 'part-time', []),
                    data_get($collectionSets, 'temp', []),
                ])->flatten(),
                // These define a single collection each, and simply rely on the type level relation above
                Demographic::VOLUNTEERS_TYPE,
                Demographic::ALL_BENEFICIARIES_TYPE,
                Demographic::TRAINING_BENEFICIARIES_TYPE,
                Demographic::INDIRECT_BENEFICIARIES_TYPE,
                Demographic::ASSOCIATES_TYPE => null,
                default => throw new InternalErrorException("Unrecognized demographic type: $demographicType"),
            };
            if (! empty($collections)) {
                $collections->each(function ($collection) use ($attributePrefix) {
                    self::resolveRelationUsing(
                        $attributePrefix . Str::studly($collection),
                        function ($entity) use ($attributePrefix, $collection) {
                            return $entity->$attributePrefix()->collection($collection);
                        }
                    );
                });
            }
        });
    }

    public function demographics()
    {
        return $this->morphMany(Demographic::class, 'demographical');
    }

    public function getAttribute($key)
    {
        $keyNormalized = Str::camel($key);
        if (array_key_exists($keyNormalized, self::DEMOGRAPHIC_TOTAL_ATTRIBUTES)) {
            $definition = self::DEMOGRAPHIC_TOTAL_ATTRIBUTES[$keyNormalized];
            $type = $definition['type'];
            $collections = is_string(self::DEMOGRAPHIC_COLLECTIONS[$type])
                ? [self::DEMOGRAPHIC_COLLECTIONS[$type]]
                : self::DEMOGRAPHIC_COLLECTIONS[$type][$definition['collections']];

            $demographicType = Str::camel($type);
            if ($this->$demographicType()->exists()) {
                return $this->sumTotalDemographicAmounts($demographicType, $collections);
            } else {
                // Fall back to the potential DB column of the same name, and finally just return 0 if there is no data.
                return parent::getAttribute($key) ?? 0;
            }
        }

        $otherDemographicType = $this->getDescriptionAttributeType($keyNormalized);
        if ($otherDemographicType != null) {
            $attributeName = Str::camel($otherDemographicType);

            return $this
                ->$attributeName()
                ->collections(self::DEMOGRAPHIC_COLLECTIONS[$otherDemographicType]['other'])
                ->orderBy('updated_at', 'desc')
                ->select('description')
                ->first()
                ?->description;
        }

        if ($this->hasAggregateDemographic($keyNormalized)) {
            $definition = self::DEMOGRAPHIC_AGGREGATE_ATTRIBUTES[$keyNormalized];
            $demographicType = Str::camel($definition['type']);

            return $this->sumTotalDemographicAmounts($demographicType, [$definition['collection']]) ?? 0;
        }

        return parent::getAttribute($key);
    }

    /**
     * @throws DemographicsException
     */
    public function setAttribute($key, $value)
    {
        $keyNormalized = Str::camel($key);
        $otherDemographicType = $this->getDescriptionAttributeType($keyNormalized);
        if ($otherDemographicType != null) {
            $collections = self::DEMOGRAPHIC_COLLECTIONS[$otherDemographicType]['other'];
            $attributeName = Str::camel($otherDemographicType);
            if (! empty($value)) {
                // If we're setting a non-null value, make sure that each of the appropriate "other" collections
                // exists for this demographic type.
                foreach ($collections as $collection) {
                    if (! $this->$attributeName()->collection($collection)->exists()) {
                        Demographic::create([
                            'demographical_type' => get_class($this),
                            'demographical_id' => $this->id,
                            'type' => $otherDemographicType,
                            'collection' => $collection,
                        ]);
                    }
                }
            }

            // We set the description on every appropriate "other" collection demographic regardless of visibility.
            $this->$attributeName()->collections($collections)->update(['description' => $value]);

            return $this;
        }

        if ($this->hasAggregateDemographic($keyNormalized)) {
            if (! is_int($value)) {
                throw new DemographicsException('Illegal attempt to update an aggregate demographic with non-integer value.');
            }
            if ($value < 0) {
                throw new DemographicsException('Illegal attempt to update an aggregate demographic with negative value.');
            }

            $definition = self::DEMOGRAPHIC_AGGREGATE_ATTRIBUTES[$keyNormalized];
            $type = $definition['type'];
            $collection = $definition['collection'];
            $demographicType = Str::camel($type);
            $demographic = $this->$demographicType()->collection($collection)->first();
            if ($demographic != null) {
                // If this appears to have been used with something more complicated than this aggregate attribute setter,
                // it's an error to try to update it this way.
                if ($demographic->entries()->count() != 2 || $demographic->entries()->whereNot('subtype', 'unknown')->exists()) {
                    throw new DemographicsException('Illegal attempt to update complicated demographics through aggregate accessor.');
                }
                // Make sure it's visible.
                if ($demographic->hidden) {
                    $demographic->update(['hidden' => false]);
                }
            } else {
                $demographic = $this->demographics()->create(['type' => $type, 'collection' => $collection, 'hidden' => false]);
            }

            $demographic->entries()->updateOrCreate(['type' => 'gender', 'subtype' => 'unknown'], ['amount' => $value]);
            $demographic->entries()->updateOrCreate(['type' => 'age', 'subtype' => 'unknown'], ['amount' => $value]);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    protected function getDescriptionAttributeType(string $keyNormalized): string | null
    {
        if (! Str::startsWith($keyNormalized, 'other') || ! Str::endsWith($keyNormalized, 'Description')) {
            return null;
        }

        $demographicType = Str::kebab(Str::before(Str::after($keyNormalized, 'other'), 'Description'));

        return in_array($demographicType, Demographic::VALID_TYPES) ? $demographicType : null;
    }

    protected function hasAggregateDemographic(string $keyNormalized): bool
    {
        if (! array_key_exists($keyNormalized, self::DEMOGRAPHIC_AGGREGATE_ATTRIBUTES)) {
            return false;
        }

        $definition = self::DEMOGRAPHIC_AGGREGATE_ATTRIBUTES[$keyNormalized];
        $type = $definition['type'];
        $collection = $definition['collection'];
        $typeDefinition = data_get(self::DEMOGRAPHIC_COLLECTIONS, $type);
        if (is_array($typeDefinition)) {
            return array_key_exists($collection, $typeDefinition);
        }
        if (is_string($typeDefinition)) {
            return $typeDefinition == $collection;
        }

        return false;
    }

    protected function sumTotalDemographicAmounts(string $demographicType, array $collections): int
    {
        // Gender is considered the canonical total value for all current types of demographics, so just pull and sum gender.
        return DemographicEntry::whereIn(
            'demographic_id',
            $this->$demographicType()->visible()->collections($collections)->select('id')
        )->gender()->sum('amount') ?? 0;
    }
}

<?php

namespace App\Models\Traits;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicEntry;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

trait HasDemographics
{
    public const DEMOGRAPHIC_ATTRIBUTES = [
        'workdaysPaid' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'paid'],
        'workdaysVolunteer' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'volunteer'],
        'workdaysDirectTotal' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'direct'],
        'workdaysConvergenceTotal' => ['type' => Demographic::WORKDAY_TYPE, 'collections' => 'convergence'],
        'directRestorationPartners' => ['type' => Demographic::RESTORATION_PARTNER_TYPE, 'collections' => 'direct'],
        'indirectRestorationPartners' => ['type' => Demographic::RESTORATION_PARTNER_TYPE, 'collections' => 'indirect'],
        'jobsFullTimeTotal' => ['type' => Demographic::JOBS_TYPE, 'collections' => 'full-time'],
        'jobsPartTimeTotal' => ['type' => Demographic::JOBS_TYPE, 'collections' => 'part-time'],
        'volunteersTotal' => ['type' => Demographic::VOLUNTEERS_TYPE, 'collections' => 'volunteer'],
        'beneficiariesTotal' => ['type' => Demographic::BENEFICIARIES_TYPE, 'collections' => 'all'],
        'beneficiariesTrainingTotal' => ['type' => Demographic::BENEFICIARIES_TYPE, 'collections' => 'training'],
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
                    $collectionSets['paid'],
                    $collectionSets['volunteer'],
                    $collectionSets['finance'],
                ])->flatten(),
                Demographic::RESTORATION_PARTNER_TYPE => collect([
                    $collectionSets['direct'],
                    $collectionSets['indirect'],
                ])->flatten(),
                Demographic::JOBS_TYPE => collect([
                    $collectionSets['full-time'],
                    $collectionSets['part-time'],
                ])->flatten(),
                Demographic::VOLUNTEERS_TYPE => collect([
                    $collectionSets['volunteer'],
                ])->flatten(),
                Demographic::BENEFICIARIES_TYPE => collect([
                    $collectionSets['all'],
                    $collectionSets['training'],
                ])->flatten(),
                default => throw new InternalErrorException("Unrecognized demographic type: $demographicType"),
            };
            $collections->each(function ($collection) use ($attributePrefix) {
                self::resolveRelationUsing(
                    $attributePrefix . Str::studly($collection),
                    function ($entity) use ($attributePrefix, $collection) {
                        return $entity->$attributePrefix()->collection($collection);
                    }
                );
            });
        });
    }

    public function demographics()
    {
        return $this->morphMany(Demographic::class, 'demographical');
    }

    public function getAttribute($key)
    {
        $attribute = parent::getAttribute($key);
        if ($attribute != null) {
            return $attribute;
        }

        $keyNormalized = Str::camel($key);
        if (array_key_exists($keyNormalized, self::DEMOGRAPHIC_ATTRIBUTES)) {
            $definition = self::DEMOGRAPHIC_ATTRIBUTES[$keyNormalized];
            $type = $definition['type'];
            $collections = self::DEMOGRAPHIC_COLLECTIONS[$type][$definition['collections']];

            return $this->sumTotalDemographicAmounts(Str::camel($type), $collections);
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

        return $attribute;
    }

    public function setAttribute($key, $value)
    {
        $otherDemographicType = $this->getDescriptionAttributeType(Str::camel($key));

        if ($otherDemographicType == null) {
            return parent::setAttribute($key, $value);
        }

        $collections = self::DEMOGRAPHIC_COLLECTIONS[$otherDemographicType]['other'];
        $attributeName = Str::camel($otherDemographicType);
        if (! empty($value)) {
            // If we're setting a non-null value, make sure that each of the appropriate "other" collections
            // exist for this demographic type.
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

    protected function getDescriptionAttributeType(string $keyNormalized): string | null
    {
        if (! Str::startsWith($keyNormalized, 'other') || ! Str::endsWith($keyNormalized, 'Description')) {
            return null;
        }

        $demographicType = Str::kebab(Str::before(Str::after($keyNormalized, 'other'), 'Description'));

        return in_array($demographicType, Demographic::VALID_TYPES) ? $demographicType : null;
    }

    protected function sumTotalDemographicAmounts(string $demographicType, array $collections): int
    {
        // Gender is considered the canonical total value for all current types of workdays, so just pull and sum gender.
        return DemographicEntry::whereIn(
            'demographic_id',
            $this->$demographicType()->visible()->collections($collections)->select('id')
        )->gender()->sum('amount');
    }
}

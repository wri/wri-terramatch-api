<?php

namespace App\Models\Traits;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\EntityModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Models that use the Demographics associations are required to have 'collection' (string) and 'hidden' (boolean)
 * columns.
 */
trait HasDemographics
{
    /**
     * @throws \Exception
     */
    public static function syncRelation(EntityModel $entity, string $property, $data, bool $hidden): void
    {
        $morph = $entity->$property();
        if (count($data) == 0) {
            $morph->delete();

            return;
        }

        // Demographic collections only have one instance per collection
        $demographicData = $data[0];
        $demographical = $morph->first();
        if ($demographical != null && $demographical->collection != $demographicData['collection']) {
            throw new \Exception(
                'Collection does not match entity property [' .
                'property collection: ' . $demographical->collection . ', ' .
                'submitted collection: ' . $demographicData['collection'] . ']'
            );
        }

        if ($demographical == null) {
            $demographical = self::create([
                $morph->getMorphType() => get_class($entity),
                $morph->getForeignKeyName() => $entity->id,
                'collection' => $demographicData['collection'],
                'hidden' => $hidden,
            ]);
        } else {
            $demographical->update(['hidden' => $hidden]);
        }

        // Make sure the incoming data is clean, and meets our expectations of one row per type/subtype/name combo.
        // The FE is not supposed to send us data with duplicates, but there has been a bug in the past that caused
        // this problem, so this extra check is just covering our bases.
        $syncData = isset($demographicData['demographics']) ? collect($demographicData['demographics'])->reduce(function ($syncData, $row) {
            $type = data_get($row, 'type');
            $subtype = data_get($row, 'subtype');
            $name = data_get($row, 'name');
            $amount = data_get($row, 'amount');

            foreach ($syncData as &$syncRow) {
                if (data_get($syncRow, 'type') === $type &&
                    data_get($syncRow, 'subtype') === $subtype &&
                    data_get($syncRow, 'name') === $name) {

                    // Keep the last value for this type/subtype/name in the incoming data set.
                    $syncRow['amount'] = $amount;

                    return $syncData;
                }
            }

            $syncData[] = $row;

            return $syncData;
        }, []) : [];

        $represented = collect();
        foreach ($syncData as $row) {
            $demographic = $demographical->demographics()->where([
                'type' => data_get($row, 'type'),
                'subtype' => data_get($row, 'subtype'),
                'name' => data_get($row, 'name'),
            ])->first();

            if ($demographic == null) {
                $represented->push($demographical->demographics()->create($row)->id);
            } else {
                $represented->push($demographic->id);
                $demographic->update(['amount' => data_get($row, 'amount')]);
            }
        }

        // Remove any existing demographic that wasn't in the submitted set.
        $demographical->demographics()->whereNotIn('id', $represented)->delete();
    }

    public function demographics(): MorphMany
    {
        return $this->morphMany(Demographic::class, 'demographical');
    }

    public function scopeCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    public function scopeCollections(Builder $query, array $collections): Builder
    {
        return $query->whereIn('collection', $collections);
    }

    public function scopeVisible($query): Builder
    {
        return $query->where('hidden', false);
    }
}

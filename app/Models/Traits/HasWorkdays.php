<?php

namespace App\Models\Traits;

use App\Models\V2\Workdays\Workday;
use Illuminate\Support\Str;

trait HasWorkdays
{
    public static function bootHasWorkdays()
    {
        foreach (array_keys(static::WORKDAY_COLLECTIONS) as $collection) {
            self::resolveRelationUsing(
                'workdays' . Str::studly($collection),
                function ($entity) use ($collection) {
                    return $entity->workdays()->collection($collection);
                }
            );
        }
    }

    public function workdays()
    {
        return $this->morphMany(Workday::class, 'workdayable');
    }

    public function getOtherWorkdaysDescriptionAttribute(): ?string
    {
        return $this
            ->workdays()
            ->whereIn('collection', self::OTHER_WORKDAY_COLLECTIONS)
            ->orderBy('updated_at', 'desc')
            ->select('description')
            ->first()
            ?->description;
    }

    public function setOtherWorkdaysDescriptionAttribute(?string $value): void
    {
        $workdaysQuery = $this->morphMany(Workday::class, 'workdayable');
        if (! empty($value)) {
            foreach (self::OTHER_WORKDAY_COLLECTIONS as $collection) {
                if (! (clone $workdaysQuery)->where('collection', $collection)->exists()) {
                    Workday::create([
                        'workdayable_type' => get_class($this),
                        'workdayable_id' => $this->id,
                        'collection' => $collection,
                    ]);
                }
            }
        }
        $workdaysQuery
            ->whereIn('collection', self::OTHER_WORKDAY_COLLECTIONS)
            ->update(['description' => $value]);
    }
}

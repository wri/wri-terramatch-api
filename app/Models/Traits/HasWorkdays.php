<?php

namespace App\Models\Traits;

use App\Models\V2\Workdays\Workday;
use App\Models\V2\Workdays\WorkdayDemographic;
use Illuminate\Support\Str;

/**
 * @property int workdays_paid
 * @property int workdays_volunteer
 * @property string other_workdays_description
 */
trait HasWorkdays
{
    public static function bootHasWorkdays()
    {
        collect([static::WORKDAY_COLLECTIONS['paid'], static::WORKDAY_COLLECTIONS['volunteer'], static::WORKDAY_COLLECTIONS['finance']])
            ->flatten()
            ->each(function ($collection) {
                self::resolveRelationUsing(
                    'workdays' . Str::studly($collection),
                    function ($entity) use ($collection) {
                        return $entity->workdays()->collection($collection);
                    }
                );
            });
    }

    public function workdays()
    {
        return $this->morphMany(Workday::class, 'workdayable');
    }

    public function getWorkdaysPaidAttribute(): int
    {
        return $this->sumTotalWorkdaysAmounts(self::WORKDAY_COLLECTIONS['paid']);
    }

    public function getWorkdaysVolunteerAttribute(): int
    {
        return $this->sumTotalWorkdaysAmounts(self::WORKDAY_COLLECTIONS['volunteer']);
    }

    public function getOtherWorkdaysDescriptionAttribute(): ?string
    {
        return $this
            ->workdays()
            ->collections(self::WORKDAY_COLLECTIONS['other'])
            ->orderBy('updated_at', 'desc')
            ->select('description')
            ->first()
            ?->description;
    }

    public function setOtherWorkdaysDescriptionAttribute(?string $value): void
    {
        if (! empty($value)) {
            foreach (self::WORKDAY_COLLECTIONS['other'] as $collection) {
                if (! $this->workdays()->collection($collection)->exists()) {
                    Workday::create([
                        'workdayable_type' => get_class($this),
                        'workdayable_id' => $this->id,
                        'collection' => $collection,
                    ]);
                }
            }
        }

        $this->workdays()->collections(self::WORKDAY_COLLECTIONS['other'])->update(['description' => $value]);
    }

    protected function sumTotalWorkdaysAmounts(array $collections): int
    {
        // Assume that the types are balanced, and just return the value from `gender`
        // Gender is considered the canonical total value for all current types of workdays, so just pull and sum gender.
        return WorkdayDemographic::whereIn(
            'workday_id',
            $this->workdays()->visible()->collections($collections)->select('id')
        )->gender()->sum('amount');
    }
}

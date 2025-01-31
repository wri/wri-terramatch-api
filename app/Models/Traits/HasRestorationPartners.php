<?php

namespace App\Models\Traits;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicEntry;
use Illuminate\Support\Str;

trait HasRestorationPartners
{
    public static function bootHasRestorationPartners()
    {
        collect([static::RESTORATION_PARTNER_COLLECTIONS['direct'], static::RESTORATION_PARTNER_COLLECTIONS['indirect']])
            ->flatten()
            ->each(function ($collection) {
                self::resolveRelationUsing(
                    'restorationPartners' . Str::studly($collection),
                    function ($entity) use ($collection) {
                        return $entity->restorationPartners()->collection($collection);
                    }
                );
            });
    }

    public function restorationPartners()
    {
        return $this->morphMany(Demographic::class, 'demographical')->type(Demographic::RESTORATION_PARTNER_TYPE);
    }

    public function getDirectRestorationPartnersAttribute(): int
    {
        return $this->sumTotalRestorationPartnersAmounts(self::RESTORATION_PARTNER_COLLECTIONS['direct']);
    }

    public function getIndirectRestorationPartnersAttribute(): int
    {
        return $this->sumTotalRestorationPartnersAmounts(self::RESTORATION_PARTNER_COLLECTIONS['indirect']);
    }

    public function getOtherRestorationPartnersDescriptionAttribute(): ?string
    {
        return $this
            ->restorationPartners()
            ->collections(self::RESTORATION_PARTNER_COLLECTIONS['other'])
            ->orderBy('updated_at', 'desc')
            ->select('description')
            ->first()
            ?->description;
    }

    public function setOtherRestorationPartnersDescriptionAttribute(?string $value): void
    {
        if (! empty($value)) {
            foreach (self::RESTORATION_PARTNER_COLLECTIONS['other'] as $collection) {
                if (! $this->restorationPartners()->collection($collection)->exists()) {
                    Demographic::create([
                        'demographical_type' => get_class($this),
                        'demographical_id' => $this->id,
                        'type' => Demographic::RESTORATION_PARTNER_TYPE,
                        'collection' => $collection,
                    ]);
                }
            }
        }

        $this->restorationPartners()->collections(self::RESTORATION_PARTNER_COLLECTIONS['other'])->update(['description' => $value]);
    }

    protected function sumTotalRestorationPartnersAmounts(array $collections): int
    {
        return DemographicEntry::whereIn(
            'demographic_id',
            $this->restorationPartners()->visible()->collections($collections)->select('id')
        )
            ->gender()
            ->sum('amount');
    }
}

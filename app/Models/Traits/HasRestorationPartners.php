<?php

namespace App\Models\Traits;

use App\Models\V2\RestorationPartners\RestorationPartner;
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
        return $this->morphMany(RestorationPartner::class, 'partnerable');
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
                    RestorationPartner::create([
                        'partnerable_type' => get_class($this),
                        'partnerable_id' => $this->id,
                        'collection' => $collection,
                    ]);
                }
            }
        }

        $this->restorationPartners()->collections(self::RESTORATION_PARTNER_COLLECTIONS['other'])->update(['description' => $value]);
    }
}

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
                    'restorationPartner' . Str::studly($collection),
                    function ($entity) use ($collection) {
                        return $entity->resotrationPartners()->collection($collection);
                    }
                );
            });
    }

    public function restorationPartners()
    {
        return $this->morphMany(RestorationPartner::class, 'partnerable');
    }
}

<?php

namespace App\Models\V2\RestorationPartners;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasUuid;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\EntityModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestorationPartner extends Model implements HandlesLinkedFieldSync
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    protected $fillable = [
        'partnerable_type',
        'partnerable_id',
        'collection',
    ];

    public static function syncRelation(EntityModel $entity, string $property, $data, bool $hidden): void
    {
        // TODO: Implement syncRelation() method.
    }

    public function partnerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function demographics(): MorphMany
    {
        return $this->morphMany(Demographic::class, 'demographical');
    }
}

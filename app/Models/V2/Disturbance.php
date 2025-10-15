<?php

namespace App\Models\V2;

use App\Http\Resources\V2\Disturbances\DisturbanceCollection;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;

class Disturbance extends Model implements EntityRelationModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasTypes;

    public $table = 'v2_disturbances';

    protected $fillable = [
        'kind',
        'collection',
        'type',
        'subtype',
        'intensity',
        'extent',
        'description',
        'action_description',
        'people_affected',
        'monetary_damage',
        'property_affected',
        'disturbance_date',
        'disturbanceable_type',
        'disturbanceable_id',
        'hidden',

        'old_id',
        'old_model',
    ];

    protected $casts = [
        'hidden' => 'boolean',
        'subtype' => 'array',
        'property_affected' => 'array',
    ];

    public static function createResourceCollection(EntityModel $entity): JsonResource
    {

        $query = Disturbance::query()
            ->where('disturbanceable_type', get_class($entity))
            ->where('disturbanceable_id', $entity->id)
            ->visible();

        return new DisturbanceCollection($query->paginate());
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function disturbanceable()
    {
        return $this->morphTo();
    }

    public function scopeVisible($query): Builder
    {
        return $query->where('hidden', false);
    }

    public function sitePolygons(): HasMany
    {
        return $this->hasMany(SitePolygon::class, 'disturbance_id');
    }
}

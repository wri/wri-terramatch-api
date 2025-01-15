<?php

namespace App\Models\V2;

use App\Http\Resources\V2\Seedings\SeedingsCollection;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;

class Seeding extends Model implements EntityRelationModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    public $table = 'v2_seedings';

    protected $fillable = [
        'name',
        'weight_of_sample',
        'seeds_in_sample',
        'amount',
        'seedable_type',
        'seedable_id',
        'hidden',

        'old_id',
        'old_model',
    ];

    protected $casts = [
        'hidden' => 'boolean',
    ];

    public static function createResourceCollection(EntityModel $entity): JsonResource
    {
        $query = Seeding::query()
            ->where('seedable_type', get_class($entity))
            ->where('seedable_id', $entity->id)
            ->visible();

        $perPage = request()->query('per_page', 15);

        return new SeedingsCollection($query->paginate($perPage));
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function seedable()
    {
        return $this->morphTo();
    }

    public function scopeVisible($query): Builder
    {
        return $query->where('hidden', false);
    }
}

<?php

namespace App\Models\V2;

use App\Http\Resources\V2\Seedings\SeedingsCollection;
use App\Models\Traits\HasUuid;
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
        'old_id',
        'old_model',
    ];

    public static function createResourceCollection(EntityModel $entity): JsonResource
    {
        $query = Seeding::query()
            ->where('seedable_type', get_class($entity))
            ->where('seedable_id', $entity->id);

        return new SeedingsCollection($query->paginate());
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function seedable()
    {
        return $this->morphTo();
    }
}

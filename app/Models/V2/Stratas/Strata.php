<?php

namespace App\Models\V2\Stratas;

use App\Http\Resources\V2\Stratas\StratasCollection;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\EntityModel;
use App\Models\V2\EntityRelationModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;

class Strata extends Model implements EntityRelationModel
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasTypes;

    protected $casts = [
        'published' => 'boolean',
        'hidden' => 'boolean',
    ];

    public $table = 'v2_stratas';

    protected $fillable = [
        'stratasable_type',
        'stratasable_id',
        'description',
        'extent',
        'hidden',
    ];

    public static function createResourceCollection(EntityModel $entity): JsonResource
    {
        $query = Strata::query()
            ->where('stratasable_type', get_class($entity))
            ->where('stratasable_id', $entity->id);

        return new StratasCollection($query->paginate());
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function stratasable()
    {
        return $this->morphTo();
    }

    public function scopeVisible($query): Builder
    {
        return $query->where('hidden', false);
    }
}

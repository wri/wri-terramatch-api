<?php

namespace App\Models\V2;

use App\Http\Resources\V2\Invasives\InvasiveCollection;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;

class Invasive extends Model implements EntityRelationModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasTypes;

    public $table = 'v2_invasives';

    protected $fillable = [
        'name',
        'type',
        'collection',
        'invasiveable_type',
        'invasiveable_id',
        'hidden',

        'old_id',
        'old_model',
    ];

    protected $casts = [
        'hidden' => 'boolean',
    ];

    public static function createResourceCollection(EntityModel $entity): JsonResource
    {
        $query = Invasive::query()
            ->where('invasiveable_type', get_class($entity))
            ->where('invasiveable_id', $entity->id);

        return new InvasiveCollection($query->paginate());
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function invasiveable()
    {
        return $this->morphTo();
    }
}

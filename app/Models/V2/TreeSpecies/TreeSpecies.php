<?php

namespace App\Models\V2\TreeSpecies;

use App\Http\Resources\V2\TreeSpecies\TreeSpeciesCollection;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\EntityModel;
use App\Models\V2\EntityRelationModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $name
 * @property mixed $amount
 */
class TreeSpecies extends Model implements EntityRelationModel
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasTypes;

    protected $casts = [
        'published' => 'boolean',
        'hidden' => 'boolean',
    ];

    public $table = 'v2_tree_species';

    protected $fillable = [
        'name',
        'amount',
        'speciesable_type',
        'speciesable_id',
        'collection',
        'hidden',

        'old_id',
        'old_model',
    ];

    public const COLLECTION_DIRECT_SEEDING = 'direct-seeding';
    public const COLLECTION_PLANTED = 'tree-planted';
    public const COLLECTION_NON_TREE = 'non-tree';
    public const COLLECTION_NURSERY = 'nursery-seedling';
    public const COLLECTION_RESTORED = 'restored';
    public const COLLECTION_PRIMARY = 'primary';

    public static $collections = [
        self::COLLECTION_DIRECT_SEEDING => 'Direct Seeding',
        self::COLLECTION_PLANTED => 'Planted',
        self::COLLECTION_NON_TREE => 'Non Tree',
        self::COLLECTION_NURSERY => 'Nursery Seedling',
        self::COLLECTION_RESTORED => 'Restored',
        self::COLLECTION_PRIMARY => 'Primary',
    ];

    public static function createResourceCollection(EntityModel $entity): JsonResource
    {
        $query = TreeSpecies::query()
            ->where('speciesable_type', get_class($entity))
            ->where('speciesable_id', $entity->id)
            ->visible();

        $filter = request()->query('filter');
        if (! empty($filter['collection'])) {
            $query->where('collection', $filter['collection']);
        }

        return new TreeSpeciesCollection($query->paginate());
    }

    public function scopeVisible($query): Builder
    {
        return $query->where('hidden', false);
    }

    public function speciesable()
    {
        return $this->morphTo();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}

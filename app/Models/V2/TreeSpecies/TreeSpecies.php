<?php

namespace App\Models\V2\TreeSpecies;

use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $name
 * @property mixed $amount
 */
class TreeSpecies extends Model
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
        'uuid',
        'name',
        'amount',
        'speciesable_type',
        'speciesable_id',
        'collection',
        'hidden',
        'taxon_id',
    ];

    public const COLLECTION_DIRECT_SEEDING = 'direct-seeding';
    public const COLLECTION_PLANTED = 'tree-planted';
    public const COLLECTION_NON_TREE = 'non-tree';
    public const COLLECTION_REPLANTING = 'replanting';
    public const COLLECTION_NURSERY = 'nursery-seedling';
    public const COLLECTION_HISTORICAL = 'historical-tree-species';

    public static $collections = [
        self::COLLECTION_DIRECT_SEEDING => 'Direct Seeding',
        self::COLLECTION_PLANTED => 'Planted',
        self::COLLECTION_NON_TREE => 'Non Tree',
        self::COLLECTION_REPLANTING => 'Replanting',
        self::COLLECTION_NURSERY => 'Nursery Seedling',
        self::COLLECTION_HISTORICAL => 'Historical Tree Species',
    ];

    public function scopeVisible($query): Builder
    {
        return $query->where('hidden', false);
    }

    public function speciesable()
    {
        return $this->morphTo();
    }

    public function taxonomicSpecies()
    {
        return $this->belongsTo(TreeSpeciesResearch::class, 'taxon_id');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}

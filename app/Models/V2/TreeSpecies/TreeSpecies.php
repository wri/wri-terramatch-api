<?php

namespace App\Models\V2\TreeSpecies;

use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreeSpecies extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasTypes;

    protected $casts = [
        'published' => 'boolean',
    ];

    public $table = 'v2_tree_species';

    protected $fillable = [
        'name',
        'amount',
        'speciesable_type',
        'speciesable_id',
        'collection',
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

    public function speciesable()
    {
        return $this->morphTo();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}

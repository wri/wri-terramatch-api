<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreeSpeciesVersion extends Model implements Version
{
    use NamedEntityTrait;
    use SoftDeletes;
    use IsVersion;

    protected $parentClass = \App\Models\TreeSpecies::class;

    public $fillable = [
        'tree_species_id',
        'status',
        'rejected_reason',
        'approved_rejected_by',
        'approved_rejected_at',
        'name',
        'is_native',
        'count',
        'price_to_plant',
        'price_to_maintain',
        'saplings',
        'site_prep',
        'survival_rate',
        'produces_food',
        'produces_firewood',
        'produces_timber',
        'owner',
        'season',
        'rejected_reason_body',
    ];

    protected $casts = [
        'approved_rejected_at' => 'datetime',
        'is_native' => 'boolean',
        'produces_food' => 'boolean',
        'produces_firewood' => 'boolean',
        'produces_timber' => 'boolean',
    ];

    public function treeSpecies()
    {
        return $this->belongsTo(TreeSpecies::class);
    }

    public function approvedRejectedBy()
    {
        return $this->belongsTo(User::class, 'approved_rejected_by');
    }
}

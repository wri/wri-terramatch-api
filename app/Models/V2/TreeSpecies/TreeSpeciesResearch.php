<?php

namespace App\Models\V2\TreeSpecies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreeSpeciesResearch extends Model
{
    use SoftDeletes;

    public $table = 'tree_species_research';

    public $primaryKey = 'taxon_id';

    public $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'taxon_id',
        'scientific_name',
        'family',
        'genus',
        'specific_epithet',
    ];
}

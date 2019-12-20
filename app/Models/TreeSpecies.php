<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasVersions;

class TreeSpecies extends Model
{
    use SoftDeletes,
        HasVersions;

    protected $versionClass = TreeSpeciesVersion::class;

    public $table = "tree_species";
    public $guarded = [];
    public $timestamps = false;
}

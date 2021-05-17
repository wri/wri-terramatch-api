<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreeSpecies extends Model
{
    use SoftDeletes, HasVersions, NamedEntityTrait;

    protected $versionClass = "App\\Models\\TreeSpeciesVersion";

    public $table = "tree_species";
    public $guarded = [];
}

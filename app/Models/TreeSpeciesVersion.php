<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\IsVersion;

class TreeSpeciesVersion extends Model implements Version
{
    use NamedEntityTrait, SoftDeletes, IsVersion;

    protected $parentClass = "App\\Models\\TreeSpecies";

    public $table = "tree_species_versions";
    public $guarded = [];
    public $dates = [
        "approved_rejected_at"
    ];
    public $casts = [
        "is_native" => "boolean",
        "produces_food" => "boolean",
        "produces_firewood" => "boolean",
        "produces_timber" => "boolean"
    ];
}

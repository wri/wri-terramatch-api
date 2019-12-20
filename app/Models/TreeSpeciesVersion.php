<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Contracts\Version;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class TreeSpeciesVersion extends Model implements Version, NamedEntity
{
    use NamedEntityTrait;

    public $table = "tree_species_versions";
    public $guarded = [];
    public $dates = [
        "approved_rejected_at"
    ];
    public $casts = [
        "produces_food" => "boolean",
        "produces_firewood" => "boolean",
        "produces_timber" => "boolean"
    ];
    public $timestamps = false;
}

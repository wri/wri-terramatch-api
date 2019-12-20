<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model implements NamedEntity
{
    use NamedEntityTrait;

    public $guarded = [];
    public $casts = [
        "matched" => "boolean"
    ];
}

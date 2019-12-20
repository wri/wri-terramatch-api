<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Match extends Model implements NamedEntity
{
    use NamedEntityTrait;

    public $table = "matches";
    public $guarded = [];
}

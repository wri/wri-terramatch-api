<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use NamedEntityTrait;

    public $guarded = [];
}

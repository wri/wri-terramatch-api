<?php

namespace App\Models;

use App\Models\Traits\SearchScopeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;

class Device extends Model
{
    public $guarded = [];
}

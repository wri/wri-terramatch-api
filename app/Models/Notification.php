<?php

namespace App\Models;

use App\Models\Traits\SearchScopeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;

class Notification extends Model
{
    public $guarded = [];
    public $casts = [
        "unread" => "boolean"
    ];
}

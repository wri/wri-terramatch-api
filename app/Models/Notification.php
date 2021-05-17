<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use NamedEntityTrait;

    public $guarded = [];
    public $casts = [
        "unread" => "boolean",
        "hidden_from_app" => "boolean"
    ];
}

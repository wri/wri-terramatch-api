<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pitch extends Model
{
    public $table = "pitches";
    public $guarded = [];
    public $casts = [
        "completed" => "boolean",
        "successful" => "boolean"
    ];
    public $dates = [
        "completed_at"
    ];
}

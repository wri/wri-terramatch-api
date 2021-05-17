<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use NamedEntityTrait;

    public $guarded = [];
    public $casts = [
        "matched" => "boolean"
    ];

    public function offer()
    {
        return $this->belongsTo("App\\Models\\Offer");
    }

    public function pitch()
    {
        return $this->belongsTo("App\\Models\\Pitch");
    }

    public function getUninitiatorAttribute(): String
    {
        return $this->initiator == "offer" ? "pitch" : "offer";
    }
}

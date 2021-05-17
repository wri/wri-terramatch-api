<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use NamedEntityTrait;

    public $guarded = [];
    public $casts = [
        "data" => "array"
    ];
    public $dates = [
        "start_date",
        "finish_date"
    ];

    public function monitoring()
    {
        return $this->belongsTo("App\\Models\\Monitoring", "monitoring_id", "id");
    }

    public function getUnnegotiatorAttribute(): String
    {
        return $this->negotiator == "offer" ? "pitch" : "offer";
    }
}

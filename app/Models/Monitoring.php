<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    use NamedEntityTrait;

    public $guarded = [];

    public function match()
    {
        return $this->belongsTo("App\\Models\\Match", "match_id", "id");
    }

    public function targets()
    {
        return $this->hasMany("App\\Models\\Target", "monitoring_id", "id");
    }

    public function progress_updates()
    {
        return $this->hasMany("App\\Models\\ProgressUpdate", "monitoring_id", "id");
    }

    public function satellite_maps()
    {
        return $this->hasMany("App\\Models\\SatelliteMap", "monitoring_id", "id");
    }

    public function getUninitiatorAttribute(): String
    {
        return $this->initiator == "offer" ? "pitch" : "offer";
    }

    public function getUnnegotiatorAttribute(): String
    {
        return $this->negotiator == "offer" ? "pitch" : "offer";
    }
}

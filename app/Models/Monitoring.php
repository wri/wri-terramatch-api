<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    use NamedEntityTrait;

    public $fillable = [
        'match_id',
        'initiator',
        'stage',
        'negotiating',
        'created_by',
    ];

    public function matched()
    {
        return $this->belongsTo(Matched::class, "match_id", "id");
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targets()
    {
        return $this->hasMany(Target::class, "monitoring_id", "id");
    }

    public function progress_updates()
    {
        return $this->hasMany(ProgressUpdate::class, "monitoring_id", "id");
    }

    public function satellite_maps()
    {
        return $this->hasMany(SatelliteMap::class, "monitoring_id", "id");
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

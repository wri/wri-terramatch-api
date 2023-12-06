<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use NamedEntityTrait;

    public $fillable = [
        'monitoring_id',
        'negotiator',
        'start_date',
        'finish_date',
        'funding_amount',
        'land_geojson',
        'accepted_at',
        'created_by',
        'accepted_by',
        'data',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'finish_date' => 'datetime',
        'data' => 'array',
    ];

    public function monitoring()
    {
        return $this->belongsTo(Monitoring::class, 'monitoring_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function getUnnegotiatorAttribute(): String
    {
        return $this->negotiator == 'offer' ? 'pitch' : 'offer';
    }
}

<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use NamedEntityTrait;

    public $fillable = [
        'organisation_id',
        'initiator',
        'offer_id',
        'pitch_id',
        'has_matched',
    ];

    public $casts = [
        'has_matched' => 'boolean',
    ];

    public function offer()
    {
        return $this->belongsTo(\App\Models\Offer::class);
    }

    public function pitch()
    {
        return $this->belongsTo(\App\Models\Pitch::class);
    }

    public function getUninitiatorAttribute(): String
    {
        return $this->initiator == 'offer' ? 'pitch' : 'offer';
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}

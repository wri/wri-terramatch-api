<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Matched extends Model
{
    use NamedEntityTrait;

    public $table = "matches";

    public $fillable = [
        'primary_interest_id',
        'secondary_interest_id',
        'created_at',
        'updated_at',
    ];

    public function interest()
    {
        return $this->belongsTo(Interest::class, "primary_interest_id");
    }

    public function secondaryInterest()
    {
        return $this->belongsTo(Interest::class, "secondary_interest_id");
    }

    public function monitoring()
    {
        return $this->hasOne(Monitoring::class, "match_id");
    }
}

<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class OfferContact extends Model
{
    use NamedEntityTrait;

    public $guarded = [];
    public $timestamps = false;

    public function team_member()
    {
        return $this->belongsTo("App\\Models\\TeamMember");
    }

    public function user()
    {
        return $this->belongsTo("App\\Models\\User");
    }
}

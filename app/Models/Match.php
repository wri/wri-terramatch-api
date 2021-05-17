<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    use NamedEntityTrait;

    public $table = "matches";
    public $guarded = [];

    public function interest()
    {
        return $this->belongsTo("App\\Models\\Interest", "primary_interest_id", "id");
    }

    public function monitoring()
    {
        return $this->hasOne("App\\Models\\Monitoring", "match_id");
    }
}

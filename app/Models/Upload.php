<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use NamedEntityTrait;

    public $guarded = [];

    public function user()
    {
        return $this->belongsTo("App\\Models\\User");
    }
}

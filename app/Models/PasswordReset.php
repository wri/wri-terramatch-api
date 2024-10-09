<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use NamedEntityTrait;

    public $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

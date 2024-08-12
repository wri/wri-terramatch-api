<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use NamedEntityTrait;

    public $fillable = [
        'user_id',
        'os',
        'uuid',
        'push_token',
        'arn',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

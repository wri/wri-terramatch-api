<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrameworkInviteCode extends Model
{
    public $fillable = [
        'code',
        'framework_id',
    ];
}

<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class NotificationsBuffer extends Model
{
    use NamedEntityTrait;

    public $table = 'notifications_buffer';

    public $fillable = [
        'identifier',
        'created_at',
        'updated_at',
    ];
}

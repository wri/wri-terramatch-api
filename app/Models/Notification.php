<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use NamedEntityTrait;

    public $fillable = [
        'user_id',
        'referenced_model',
        'referenced_model_id',
        'title',
        'body',
        'action',
        'unread',
        'hidden_from_app',
        'created_at',
        'updated_at',
    ];

    public $casts = [
        'unread' => 'boolean',
        'hidden_from_app' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

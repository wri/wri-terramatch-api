<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use NamedEntityTrait;
    use HasFactory;

    public $fillable = [
        'user_id',
        'location',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

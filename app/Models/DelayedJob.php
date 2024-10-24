<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelayedJob extends Model
{
    use HasFactory;

    protected $table = 'delayed_jobs';

    protected $fillable = [
        'uuid',
        'status',
        'status_code',
        'payload',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];
}

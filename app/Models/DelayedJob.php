<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelayedJob extends Model
{
    use HasFactory;
    use HasUuid;

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

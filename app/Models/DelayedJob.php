<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelayedJob extends Model
{
    use HasFactory;
    use HasUuid;

    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCEEDED = 'succeeded';


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

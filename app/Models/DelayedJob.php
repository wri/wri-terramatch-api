<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Models\V2\User;
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

    protected $fillable = ['uuid', 'status', 'status_code', 'payload', 'entity_type', 'entity_id', 'created_by', 'is_acknowledged'];

    protected $casts = [
        'uuid' => 'string',
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

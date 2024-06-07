<?php

namespace App\Models\V2\AuditStatus;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditStatus extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    public $table = 'status';

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'status',
        'comment',
        'first_name',
        'last_name',
        'type',
        'is_submitted',
        'is_active',
        'request_removed',
        'date_created',
        'created_by',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}

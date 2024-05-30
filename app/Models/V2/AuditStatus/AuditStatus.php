<?php

namespace App\Models\V2\AuditStatus;

use App\Models\V2\AuditAttachment\AuditAttachment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditStatus extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'status';

    protected $fillable = [
        'id',
        'entity',
        'entity_uuid',
        'status',
        'comment',
        'date_created',
        'created_by',
        'type',
        'is_submitted',
        'is_active',
        'first_name',
        'last_name',
        'request_removed',
    ];

    public function auditAttachments()
    {
        return $this->hasMany(AuditAttachment::class, 'entity_id', 'id');
    }
}

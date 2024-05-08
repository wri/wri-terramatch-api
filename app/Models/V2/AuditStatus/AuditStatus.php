<?php

namespace App\Models\V2\AuditStatus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use App\Models\V2\Audits\AuditAttachment;
// use App\Models\V2\AuditStatus\AuditAttachment;

class AuditStatus extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'status';

    protected $fillable = [
        'id',
        'entity_uuid',
        'status',
        'comment',
        'attachment_url',
        'date_created',
        'created_by',
    ];
}

<?php

namespace App\Models\V2\AuditAttachment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\V2\AuditStatus\AuditStatus;

class AuditAttachment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'status';

    protected $fillable = [
        'id',
        'attachment',
        'date_created',
        'created_by',
    ];

    public function auditStatus()
    {
        return $this->belongsTo(AuditStatus::class, 'id', 'id');
    }
}
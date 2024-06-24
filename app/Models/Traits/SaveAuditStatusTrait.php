<?php

namespace App\Models\Traits;

use App\Models\V2\AuditStatus\AuditStatus;
use Illuminate\Support\Facades\Auth;

trait SaveAuditStatusTrait
{
    public function saveAuditStatus($auditable_type, $auditable_id, $status, $comment, $type = null, $is_active = null, $request_removed = null, $is_submitted = null)
    {
        return AuditStatus::create([
            'auditable_type' => $auditable_type,
            'auditable_id' => $auditable_id,
            'status' => $status,
            'comment' => $comment,
            'date_created' => now(),
            'created_by' => Auth::user()->email_address,
            'type' => $type,
            'is_submitted' => $is_submitted,
            'is_active' => $is_active,
            'first_name' => Auth::user()->first_name,
            'last_name' => Auth::user()->last_name,
            'request_removed' => $request_removed,
        ]);
    }
}

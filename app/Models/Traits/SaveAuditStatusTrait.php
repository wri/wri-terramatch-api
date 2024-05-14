<?php

namespace App\Models\Traits;

use App\Models\V2\AuditStatus\AuditStatus;
use Illuminate\Support\Facades\Auth;

trait SaveAuditStatusTrait
{
    public function saveAuditStatus($entity, $entity_uuid, $status, $comment, $attachment_url = null)
    {
        return AuditStatus::create([
            'entity' => $entity,
            'entity_uuid' => $entity_uuid,
            'status' => $status,
            'comment' => $comment,
            'attachment_url' => $attachment_url,
            'date_created' => now(),
            'created_by' => Auth::user()->email_address,
        ]);
    }
}

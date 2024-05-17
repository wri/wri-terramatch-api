<?php

namespace App\Models\Traits;

use App\Models\V2\AuditAttachment\AuditAttachment;
use Illuminate\Support\Facades\Auth;

trait SaveAuditAttachmentTrait
{
    public function saveAuditAttachment($entity_uuid = null, $attachment = null)
    {
        return AuditAttachment::create([
            'entity_id' => $entity_uuid,
            'attachment' => $attachment,
            'date_created' => now(),
            'created_by' => Auth::user()->email_address,
        ]);
    }
}

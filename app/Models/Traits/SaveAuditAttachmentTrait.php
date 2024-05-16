<?php

namespace App\Models\Traits;

use App\Models\V2\AuditAttachment\AuditAttachment;
use Illuminate\Support\Facades\Auth;

trait SaveAuditAttachmentTrait
{
    public function saveAuditAttachment($comment_id = null, $attachment = null)
    {
        return AuditAttachment::create([
            'comment_id' => $comment_id,
            'attachment' => $attachment,
            'date_created' => now(),
            'created_by' => Auth::user()->email_address,
        ]);
    }
}

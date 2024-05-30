<?php

namespace App\Http\Controllers\V2\Attachment;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AttachmentResource;
use App\Models\V2\AuditAttachment\AuditAttachment;
use Illuminate\Http\Request;

class GetAttachmentController extends Controller
{
    public function __invoke(Request $request)
    {
        $attachment = AuditAttachment::orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return AttachmentResource::collection($attachment);
    }
}

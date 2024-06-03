<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusResource;
use App\Models\Traits\SaveAuditAttachmentTrait;
use App\Models\Traits\SaveAuditStatusTrait;
use App\Models\V2\AuditStatus\AuditStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StoreAuditStatusController extends Controller
{
    use SaveAuditStatusTrait;
    use SaveAuditAttachmentTrait;

    public function __invoke(Request $request): AuditStatusResource
    {
        $body = $request->all();
        if ($body['type'] === 'change-request') {
            AuditStatus::where([
                ['entity_uuid', $body['entity_uuid']],
                ['type', 'change-request'],
                ['is_active', true],
            ])->update(['is_active' => false]);
            $auditStatusresponse = $this->saveAuditStatus($body['entity'], $body['entity_uuid'], $body['status'], $body['comment'], $body['type'], $body['is_active'], $body['request_removed']);
        } else {
            $auditStatusresponse = $this->saveAuditStatus($body['entity'], $body['entity_uuid'], $body['status'], $body['comment'], $body['type']);
        }

        if ($request->file('file')) {
            foreach ($request->file('file') as $file) {
                if ($file !== null) {
                    try {
                        $fileName = time() . $file->getClientOriginalName();
                        $filePath = 'public/' . $fileName;

                        Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');
                        $urlPath = Storage::disk('s3')->url($filePath);

                        $this->saveAuditAttachment($auditStatusresponse->id, $file->getClientOriginalName(), $urlPath);
                    } catch (\Exception $e) {
                        Log::error('Error uploading file: ' . $e->getMessage());

                        throw $e;
                    }
                }
            }
        }

        return new AuditStatusResource($auditStatusresponse);
    }
}
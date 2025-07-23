<?php

namespace App\Observers;

use App\Jobs\NotifyGreenhouseJob;
use App\Models\V2\User;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    public function deleting(Media $media): void
    {
        Log::info('deleting: ' . $media->uuid);
        if (empty($media->created_by)) {
            Log::info('no created by');

            return;
        }

        $owner = User::where('id', $media->created_by)->first();
        if ($owner?->primaryRole?->name != 'greenhouse-service-account') {
            Log::info('not owned by GH');

            return;
        }

        Log::info('dispatching');
        NotifyGreenhouseJob::dispatch('notifyMediaDeleted', $media->uuid);
    }

    public function saving(Media $media): void
    {
        $media->type = $this->mapType($media->mime_type);
    }

    private function mapType($mime)
    {
        if (strpos($mime, 'vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.sheet') !== false) {
            return 'xlsx';
        }

        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/csv' => 'csv',
            'text/plain' => 'txt',
        ];

        if (isset($map[$mime])) {
            return $map[$mime];
        }

        if (strpos($mime, '/') !== false) {
            return substr($mime, strpos($mime, '/') + 1);
        }

        return 'other';
    }
}

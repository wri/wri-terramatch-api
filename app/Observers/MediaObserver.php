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
}

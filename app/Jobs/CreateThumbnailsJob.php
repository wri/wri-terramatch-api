<?php

namespace App\Jobs;

use App\Models\ProgressUpdate as ProgressUpdateModel;
use App\Services\FileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManagerStatic as ImageService;

class CreateThumbnailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $progressUpdate;

    public function __construct(ProgressUpdateModel $progressUpdate)
    {
        $this->progressUpdate = $progressUpdate;
    }

    public function handle()
    {
        $fileService = App::make(\App\Services\FileService::class);
        $imageService = ImageService::configure(['driver' => 'imagick']);
        $images = $this->progressUpdate->images;
        foreach ($images as &$image) {
            $remoteImage = $image['image'];
            $localImage = $fileService->clone($remoteImage);
            $localThumbnailExtension = explode_pop('.', $localImage);
            $localThumbnail = '/tmp/' . Str::random(64) . '.' . $localThumbnailExtension;
            $imageService
                ->make($localImage)
                ->resize(512, 512, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($localThumbnail);
            $localThumbnailMimeType = FileService::MIME_TYPES[$localThumbnailExtension];
            $remoteThumbnail = $fileService->create($localThumbnail, $localThumbnailMimeType);
            $image['thumbnail'] = $remoteThumbnail;
            unlink($localImage);
            unlink($localThumbnail);
        }
        $this->progressUpdate->images = $images;
        $this->progressUpdate->saveOrFail();
    }
}

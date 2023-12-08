<?php

namespace App\Jobs;

use App\Models\SatelliteMap as SatelliteMapModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as ImageService;

class ConvertSatelliteMapJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $satelliteMap;

    public function __construct(SatelliteMapModel $satelliteMap)
    {
        $this->satelliteMap = $satelliteMap;
    }

    public function handle()
    {
        $fileService = App::make(\App\Services\FileService::class);
        $imageService = ImageService::configure(['driver' => 'imagick']);
        $remoteTiff = $this->satelliteMap->map;
        $localTiff = $fileService->clone($remoteTiff);
        $localJpeg = '/tmp/' . Str::random(64) . '.jpg';
        $imageService->make($localTiff)->save($localJpeg);
        $remoteJpeg = $fileService->create($localJpeg, 'image/jpeg');
        $this->satelliteMap->map = $remoteJpeg;
        $this->satelliteMap->saveOrFail();
        unlink($localTiff);
        unlink($localJpeg);
        $fileService->delete($remoteTiff);
    }
}

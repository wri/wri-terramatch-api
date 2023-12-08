<?php

namespace App\Jobs;

use App\Models\SatelliteMonitor as SatelliteMonitorModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as ImageService;

class ConvertSatelliteMonitorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $satelliteMonitor;

    public function __construct(SatelliteMonitorModel $satelliteMonitor)
    {
        $this->satelliteMonitor = $satelliteMonitor;
    }

    public function handle()
    {
        $fileService = App::make(\App\Services\FileService::class);
        $imageService = ImageService::configure(['driver' => 'imagick']);
        $remoteTiff = $this->satelliteMonitor->map;
        $localTiff = $fileService->clone($remoteTiff);
        $localJpeg = '/tmp/' . Str::random(64) . '.jpg';
        $imageService->make($localTiff)->save($localJpeg);
        $remoteJpeg = $fileService->create($localJpeg, 'image/jpeg');
        $this->satelliteMonitor->map = $remoteJpeg;
        $this->satelliteMonitor->saveOrFail();
        unlink($localTiff);
        unlink($localJpeg);
        $fileService->delete($remoteTiff);
    }
}

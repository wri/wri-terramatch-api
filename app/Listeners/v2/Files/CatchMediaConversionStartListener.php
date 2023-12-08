<?php

namespace App\Listeners\v2\Files;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Conversions\Events\ConversionWillStart;

class CatchMediaConversionStartListener
{
    public function handle(ConversionWillStart $event)
    {
        $media = $event->media;
        $conversionName = $event->conversion->getName();

        // Log that a conversion is about to start
        Log::info("Conversion '$conversionName' is starting for media id {$media->id}");
    }
}

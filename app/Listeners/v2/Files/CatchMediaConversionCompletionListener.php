<?php

namespace App\Listeners\v2\Files;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;

class CatchMediaConversionCompletionListener
{
    public function handle(ConversionHasBeenCompleted $event)
    {
        $media = $event->media;
        $conversionName = $event->conversion->getName();

        // Log that the conversion has been completed
        Log::info("Conversion '$conversionName' has been completed for media id {$media->id}");
    }
}

<?php

namespace App\Models\V2;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

/**
 * @property array $fileConfiguration
 */
interface MediaModel extends HasMedia
{
    public function addMediaFromRequest(string $key): FileAdder;
}

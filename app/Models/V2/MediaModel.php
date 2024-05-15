<?php

namespace App\Models\V2;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

/**
 * @property array $fileConfiguration
 */
ini_set('upload_max_filesize', '30M');
interface MediaModel extends HasMedia
{
    public function addMediaFromRequest(string $key): FileAdder;
}

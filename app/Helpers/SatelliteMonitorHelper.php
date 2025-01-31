<?php

namespace App\Helpers;

use App\Models\Programme;
use App\Models\Site;

class SatelliteMonitorHelper
{
    public const POLYMORPHIC_MODELS = [
        'programme' => Programme::class,
        'site' => Site::class,

    ];

    public static function translateModel(String $data): String
    {
        return ($data !== Programme::class && $data !== Site::class) ? self::POLYMORPHIC_MODELS[$data] : $data;
    }
}

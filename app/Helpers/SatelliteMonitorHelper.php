<?php

namespace App\Helpers;

use App\Models\Programme;
use App\Models\Site;
use App\Models\Terrafund\TerrafundProgramme;

class SatelliteMonitorHelper
{
    public const POLYMORPHIC_MODELS = [
        'programme' => Programme::class,
        'site' => Site::class,
        'terrafund_programme' => TerrafundProgramme::class,

    ];

    public static function translateModel(String $data): String
    {
        return ($data !== Programme::class && $data !== Site::class && $data !== TerrafundProgramme::class) ? self::POLYMORPHIC_MODELS[$data] : $data;
    }
}

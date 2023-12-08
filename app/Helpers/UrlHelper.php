<?php

namespace App\Helpers;

class UrlHelper
{
    private function __construct()
    {
    }

    public static function repair(?String $url): ?string
    {
        if (is_null($url)) {
            return $url;
        }
        $startsWithHttp = strtolower(substr($url, 0, 7)) == 'http://';
        $startsWithHttps = strtolower(substr($url, 0, 8)) == 'https://';
        if ($startsWithHttp || $startsWithHttps) {
            return $url;
        }

        return 'http://' . $url;
    }
}

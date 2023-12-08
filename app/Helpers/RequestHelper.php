<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestHelper
{
    private function __construct()
    {
    }

    public static function isAndroid(Request $request): Bool
    {
        return
            ! $request->hasHeader('Referer') &&
            Str::startsWith($request->header('User-Agent'), 'okhttp');
    }

    public static function isIos(Request $request): Bool
    {
        return
            ! $request->hasHeader('Referer') &&
            Str::contains($request->header('User-Agent'), 'CFNetwork') &&
            Str::contains($request->header('User-Agent'), 'Darwin');
    }
}

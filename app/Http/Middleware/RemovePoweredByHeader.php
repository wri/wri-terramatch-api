<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class RemovePoweredByHeader
{
    public function handle(Request $request, Closure $next)
    {
        if (!in_array(Config::get("app.env"), ["testing", "pipelines", "local"])) {
            header_remove("X-Powered-By");
        }
        return $next($request);
    }
}

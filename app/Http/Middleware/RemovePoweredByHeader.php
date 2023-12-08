<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RemovePoweredByHeader
{
    public function handle(Request $request, Closure $next)
    {
        if (! in_array(config('app.env'), ['testing', 'pipelines', 'local'])) {
            header_remove('X-Powered-By');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class I18nMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        if ($request->query('lang')) {
            App::setLocale($request->query('lang'));
        }

        return $next($request);
    }
}

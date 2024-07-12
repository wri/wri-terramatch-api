<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! in_array($request->user()->role, ['admin', 'terrafund_admin', 'project-manager'])) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}

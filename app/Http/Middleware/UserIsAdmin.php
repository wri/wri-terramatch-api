<?php

namespace App\Http\Middleware;

use App\Models\V2\User;
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
        /** @var User $user */
        $user = $request->user();
        if (empty($user) || (! $user->isAdmin && ! $user->hasRole('project-manager'))) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}

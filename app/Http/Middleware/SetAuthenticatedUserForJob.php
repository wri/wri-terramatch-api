<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetAuthenticatedUserForJob
{
    /**
     * Handle the job middleware.
     *
     * @param  mixed  $job
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($job, Closure $next)
    {
        if (isset($job->authUserId)) {
            Auth::onceUsingId($job->authUserId);
        }

        return $next($job);
    }
}

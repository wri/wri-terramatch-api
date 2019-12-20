<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Cache\RateLimiter;

class RateLimiterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind("Illuminate\\Cache\\RateLimiter", function(Application $app) {
            $cacheManager = $app->make("Illuminate\\Cache\\CacheManager");
            $cache = $cacheManager->store("database");
            $rateLimiter = new RateLimiter($cache);
            return $rateLimiter;
        });
    }
}
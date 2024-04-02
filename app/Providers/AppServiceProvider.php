<?php

namespace App\Providers;

use App\Auth\ServiceAccountGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if ($this->app->environment(['production', 'staging', 'test', 'development', 'dev', 'demo'])) {
            URL::forceScheme('https');
        }

        Auth::extend('service-account', function (Application $app, string $name, array $config) {
            return new ServiceAccountGuard($app['request']);
        });
    }
}

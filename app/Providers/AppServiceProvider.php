<?php

namespace App\Providers;

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
    }
}

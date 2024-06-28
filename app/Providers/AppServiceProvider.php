<?php

namespace App\Providers;

use App\Auth\ServiceAccountGuard;
use App\Models\V2\Sites\SitePolygon;
use App\Observers\MediaObserver;
use App\Observers\SitePolygonObserver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

        SitePolygon::observe(SitePolygonObserver::class);
        Media::observe(MediaObserver::class);
    }
}

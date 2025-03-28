<?php

namespace App\Providers;

use App\Auth\ServiceAccountGuard;
use App\Models\DelayedJob;
use App\Models\V2\Sites\SitePolygon;
use App\Observers\MediaObserver;
use App\Observers\SitePolygonObserver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
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
        Queue::failing(function (JobFailed $event) {
            $jobName = $event->job->resolveName();
            $jobId = $event->job->uuid(); // Get UUID if available
            $exceptionMessage = $event->exception->getMessage();

            $rawBody = $event->job->getRawBody();
            ;

            $payload = json_decode($rawBody, true);

            try {
                $command = unserialize($payload['data']['command']);
                $reflection = new ReflectionClass($command);
                if ($reflection->hasProperty('delayed_job_id')) {
                    $property = $reflection->getProperty('delayed_job_id');
                    $property->setAccessible(true);
                    $delayedJobId = $property->getValue($command);
                } else {
                    $delayedJobId = null;
                }

            } catch (\Exception $e) {
                Log::error('Error deserializing command: ' . $e->getMessage());

                return;
            }

            $delayedJob = DelayedJob::where('id', $delayedJobId)->first();
            if ($delayedJob) {
                $delayedJob->update([
                    'status' => DelayedJob::STATUS_FAILED,
                    'payload' => json_encode(['error' => $exceptionMessage]),
                    'status_code' => 500,
                ]);
            }
        });
    }
}

<?php

namespace App\Providers;

use Aws\Sns\SnsClient;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SnsClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('CustomSnsClient', function (Application $app) {
            $snsClient = new SnsClient([
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'region' => config('filesystems.disks.s3.region'),
                'version' => 'latest',
                'endpoint' => config('app.sns.prefix'),
                'use_path_style_endpoint' => true,
            ]);

            return $snsClient;
        });
    }
}

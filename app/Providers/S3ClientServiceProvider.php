<?php

namespace App\Providers;

use Aws\S3\S3Client;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class S3ClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('CustomS3Client', function (Application $app) {
            $s3Client = new S3Client([
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'region' => config('filesystems.disks.s3.region'),
                'version' => 'latest',
                'endpoint' => config('app.s3.prefix'),
                'use_path_style_endpoint' => true,
            ]);

            return $s3Client;
        });
    }
}

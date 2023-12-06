<?php

namespace App\Providers;

use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ElasticTranscoderClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('CustomElasticTranscoderClient', function (Application $app) {
            $endpoint = config('app.elastic_transcoder.prefix');
            $elasticTranscoderClient = new ElasticTranscoderClient([
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
                'region' => config('filesystems.disks.s3.region'),
                'version' => 'latest',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
            ]);

            return $elasticTranscoderClient;
        });
    }
}

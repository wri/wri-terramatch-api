<?php

namespace App\Providers;

use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;

class ElasticTranscoderClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind("CustomElasticTranscoderClient", function(Application $app) {
            $endpoint = Config::get("app.elastic_transcoder.prefix");
            $elasticTranscoderClient = new ElasticTranscoderClient([
                "credentials" => [
                    "key" => Config::get("filesystems.disks.s3.key"),
                    "secret" => Config::get("filesystems.disks.s3.secret"),
                ],
                "region" => Config::get("filesystems.disks.s3.region"),
                "version" => "latest",
                "endpoint" => $endpoint,
                "use_path_style_endpoint" => true
            ]);
            return $elasticTranscoderClient;
        });
    }
}
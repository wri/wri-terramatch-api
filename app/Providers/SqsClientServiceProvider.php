<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Aws\Sqs\SqsClient;

class SqsClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind("CustomSqsClient", function(Application $app) {
            $config = $app->make("Illuminate\\Config\\Repository");
            $endpoint = str_replace("/queue", "", $config->get("app.sqs.prefix"));
            $sqsClient = new SqsClient([
                "credentials" => [
                    "key" => $config->get("filesystems.disks.s3.key"),
                    "secret" => $config->get("filesystems.disks.s3.secret"),
                ],
                "region" => $config->get("filesystems.disks.s3.region"),
                "version" => "latest",
                "endpoint" => $endpoint,
                "use_path_style_endpoint" => true
            ]);
            return $sqsClient;
        });
    }
}
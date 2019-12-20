<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Aws\S3\S3Client;

class S3ClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind("CustomS3Client", function(Application $app) {
            $config = $app->make("Illuminate\\Config\\Repository");
            $s3Client = new S3Client([
                "credentials" => [
                    "key" => $config->get("filesystems.disks.s3.key"),
                    "secret" => $config->get("filesystems.disks.s3.secret"),
                ],
                "region" => $config->get("filesystems.disks.s3.region"),
                "version" => "latest",
                "endpoint" => $config->get("app.s3.prefix"),
                "use_path_style_endpoint" => true
            ]);
            return $s3Client;
        });
    }
}
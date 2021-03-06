<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

class S3ClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind("CustomS3Client", function(Application $app) {
            $s3Client = new S3Client([
                "credentials" => [
                    "key" => Config::get("filesystems.disks.s3.key"),
                    "secret" => Config::get("filesystems.disks.s3.secret"),
                ],
                "region" => Config::get("filesystems.disks.s3.region"),
                "version" => "latest",
                "endpoint" => Config::get("app.s3.prefix"),
                "use_path_style_endpoint" => true
            ]);
            return $s3Client;
        });
    }
}
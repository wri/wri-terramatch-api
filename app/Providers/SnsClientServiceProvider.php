<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Aws\Sns\SnsClient;
use Illuminate\Support\Facades\Config;

class SnsClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind("CustomSnsClient", function(Application $app) {
            $snsClient = new SnsClient([
                "credentials" => [
                    "key" => Config::get("filesystems.disks.s3.key"),
                    "secret" => Config::get("filesystems.disks.s3.secret"),
                ],
                "region" => Config::get("filesystems.disks.s3.region"),
                "version" => "latest",
                "endpoint" => Config::get("app.sns.prefix"),
                "use_path_style_endpoint" => true
            ]);
            return $snsClient;
        });
    }
}
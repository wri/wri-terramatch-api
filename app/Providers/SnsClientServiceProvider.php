<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Aws\Sns\SnsClient;

class SnsClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind("CustomSnsClient", function(Application $app) {
            $config = $app->make("Illuminate\\Config\\Repository");
            $snsClient = new SnsClient([
                "credentials" => [
                    "key" => $config->get("filesystems.disks.s3.key"),
                    "secret" => $config->get("filesystems.disks.s3.secret"),
                ],
                "region" => $config->get("filesystems.disks.s3.region"),
                "version" => "latest",
                "endpoint" => $config->get("app.sns.prefix"),
                "use_path_style_endpoint" => true
            ]);
            return $snsClient;
        });
    }
}
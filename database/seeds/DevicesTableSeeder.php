<?php

use Illuminate\Database\Seeder;
use App\Models\Device as DeviceModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class DevicesTableSeeder extends Seeder
{
    public function run()
    {
        $device = new DeviceModel();
        $device->id = 1;
        $device->user_id = 3;
        $device->os = "android";
        $device->uuid = Str::random();
        $device->push_token = Str::random();
        $pushService = App::make("App\\Services\\PushService");
        $device->arn = $pushService->fetchEndpointArn($device->os, $device->push_token);
        $device->arn = "qwertyuiop";
        $device->saveOrFail();
    }
}

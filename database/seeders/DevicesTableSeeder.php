<?php

namespace Database\Seeders;

use App\Models\Device as DeviceModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class DevicesTableSeeder extends Seeder
{
    public function run()
    {
        $device = new DeviceModel();
        $device->id = 1;
        $device->user_id = 3;
        $device->os = 'android';
        $device->uuid = Str::random(16);
        $device->push_token = Str::random(16);
        $pushService = App::make(\App\Services\PushService::class);
        $device->arn = $pushService->fetchEndpointArn($device->os, $device->push_token);
        $device->arn = 'qwertyuiop';
        $device->saveOrFail();
    }
}

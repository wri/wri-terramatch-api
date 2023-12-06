<?php

namespace App\Resources;

use App\Models\Device as DeviceModel;

class DeviceResource extends Resource
{
    public function __construct(DeviceModel $device)
    {
        $this->id = $device->id;
        $this->user_id = $device->user_id;
        $this->os = $device->os;
        $this->uuid = $device->uuid;
        $this->push_token = $device->push_token;
        $this->created_at = $device->created_at;
    }
}

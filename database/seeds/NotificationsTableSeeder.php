<?php

use Illuminate\Database\Seeder;
use App\Models\Notification as NotificationModel;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        $notification = new NotificationModel();
        $notification->id = 1;
        $notification->user_id = 3;
        $notification->title = "Lorem ipsum";
        $notification->body = "Lorem ipsum dolor sit amet";
        $notification->saveOrFail();
    }
}

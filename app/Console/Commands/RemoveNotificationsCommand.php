<?php

namespace App\Console\Commands;

use App\Models\Notification as NotificationModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

class RemoveNotificationsCommand extends Command
{
    protected $signature = 'remove-notifications';

    protected $description = 'Removes notifications older than 90 days';

    public function handle(): int
    {
        $past = new DateTime('now - 90 days', new DateTimeZone('UTC'));
        NotificationModel::where('created_at', '<=', $past)->where('unread', 0)->delete();

        return 0;
    }
}

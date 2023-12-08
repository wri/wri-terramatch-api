<?php

namespace App\Console\Commands;

use App\Models\NotificationsBuffer as NotificationsBufferModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

class RemoveNotificationsBuffersCommand extends Command
{
    protected $signature = 'remove-notifications-buffers';

    protected $description = 'Removes notifications buffers older than 5 minutes';

    public function handle(): int
    {
        $past = new DateTime('now - 5 minutes', new DateTimeZone('UTC'));
        NotificationsBufferModel::where('created_at', '<=', $past)->delete();

        return 0;
    }
}

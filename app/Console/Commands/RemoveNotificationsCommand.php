<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification as NotificationModel;
use DateTime;
use DateTimeZone;

class RemoveNotificationsCommand extends Command
{
    protected $signature = "remove-notifications";
    protected $description = "Removes notifications older than 90 days";

    private $notificationModel = null;

    public function __construct(NotificationModel $notificationModel)
    {
        parent::__construct();
        $this->notificationModel = $notificationModel;
    }

    public function handle()
    {
        $past = new DateTime("now - 90 days", new DateTimeZone("UTC"));
        $this->notificationModel->where("created_at", "<=", $past)
            ->where('unread', 0)
            ->delete();
    }
}

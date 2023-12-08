<?php

namespace App\Helpers;

use App\Models\NotificationsBuffer as NotificationsBufferModel;
use DateTime;
use DateTimeZone;

class NotificationHelper
{
    private function __construct()
    {
    }

    /**
     * This method detects whether an identical notification job has run within
     * the last minute or so. This stops one batch of changes triggering
     * multiple jobs. It's a little bit crude but seems to work.
     */
    public static function isDuplicate(string $identifier): bool
    {
        $past = new DateTime('now - 1 minute', new DateTimeZone('UTC'));
        $count = NotificationsBufferModel::where('identifier', '=', $identifier)
            ->where('created_at', '>=', $past)
            ->count();
        if ($count >= 1) {
            return true;
        } else {
            $notificationsBuffer = new NotificationsBufferModel();
            $notificationsBuffer->identifier = $identifier;
            $notificationsBuffer->saveOrFail();

            return false;
        }
    }
}

<?php

namespace App\Listeners\v2\Organisation;

use App\Events\V2\Organisation\OrganisationUserJoinRequestEvent;
use App\Models\Notification as NotificationModel;

class OrganisationUserJoinRequestNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrganisationUserJoinRequestEvent $event)
    {
        $organisation = $event->organisation;
        foreach ($organisation->owners as $owner) {
            $notification = NotificationModel::create([
                'user_id' => $owner->id,
                'title' => 'A user has requested to join your organization',
                'body' => 'A user has requested to join your organization. Please go to the ‘Meet the Team’ page to review this request.',
                'action' => 'user_join_organisation_requested',
                'referenced_model' => get_class($organisation),
                'referenced_model_id' => $organisation->id,
            ]);
        }
    }
}

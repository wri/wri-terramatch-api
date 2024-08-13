<?php

namespace App\Http\Resources\V2\Organisation;

use Illuminate\Http\Resources\Json\JsonResource;

class PendingApplicationResource extends JsonResource
{
    public function __construct($hasPendingApplication, $message, $organisation = null, $status = null)
    {
        $this->has_pending_application = $hasPendingApplication;
        $this->message = $message;
        $this->organisation_name = $organisation ? $organisation->name : null;
        $this->user_status = $status;
        $this->organisation_status = $organisation ? $organisation->status : null;
        $this->organisation_uuid = $organisation ? $organisation->uuid : null;
    }

    public function toArray($request)
    {
        return [
            'has_pending_application' => $this->has_pending_application,
            'message' => $this->message,
            'organisation_name' => $this->organisation_name,
            'user_status' => $this->user_status,
            'organisation_status' => $this->organisation_status,
            'organisation_uuid' => $this->organisation_uuid,
        ];
    }
}

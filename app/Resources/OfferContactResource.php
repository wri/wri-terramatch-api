<?php

namespace App\Resources;

use App\Models\OfferContact as OfferContactModel;
use Exception;

class OfferContactResource
{
    public function __construct(OfferContactModel $offerContact)
    {
        $this->id = $offerContact->id;
        $this->offer_id = $offerContact->offer_id;
        /**
         * This sections dynamically sets either the team_member_id property or
         * the user_id property depending on the data coming from the model. By
         * doing it this way we avoid a redundant null value being returned.
         */
        $hasTeamMember = ! is_null($offerContact->team_member_id);
        $hasUser = ! is_null($offerContact->user_id);
        if ($hasTeamMember && ! $hasUser) {
            $this->team_member_id = $offerContact->team_member_id;
            $model = $offerContact->team_member;
        } elseif (! $hasTeamMember && $hasUser) {
            $this->user_id = $offerContact->user_id;
            $model = $offerContact->user;
        } else {
            throw new Exception();
        }
        /**
         * This section sets the visible contact details. These are common
         * properties between users and team_members so we don't need model
         * specific logic.
         */
        $this->first_name = $model->first_name;
        $this->last_name = $model->last_name;
        $this->job_role = $model->job_role;
        $this->avatar = $model->avatar;
        $this->facebook = $model->facebook;
        $this->twitter = $model->twitter;
        $this->linkedin = $model->linkedin;
        $this->instagram = $model->instagram;
    }
}

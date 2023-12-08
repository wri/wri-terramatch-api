<?php

namespace App\Resources;

use App\Models\PitchContact as PitchContactModel;
use Exception;

class PitchContactResource
{
    public function __construct(PitchContactModel $pitchContact)
    {
        $this->id = $pitchContact->id;
        $this->pitch_id = $pitchContact->pitch_id;
        /**
         * This sections dynamically sets either the team_member_id property or
         * the user_id property depending on the data coming from the model. By
         * doing it this way we avoid a redundant null value being returned.
         */
        $hasTeamMember = ! is_null($pitchContact->team_member_id);
        $hasUser = ! is_null($pitchContact->user_id);
        if ($hasTeamMember && ! $hasUser) {
            $this->team_member_id = $pitchContact->team_member_id;
            $model = $pitchContact->team_member;
        } elseif (! $hasTeamMember && $hasUser) {
            $this->user_id = $pitchContact->user_id;
            $model = $pitchContact->user;
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

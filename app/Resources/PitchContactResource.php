<?php

namespace App\Resources;

use App\Models\PitchContact as PitchContactModel;
use Exception;

class PitchContactResource
{
    public $id = null;
    public $pitch_id = null;
    // public $team_member_id = null;
    // public $user_id = null;

    public function __construct(PitchContactModel $pitchContact)
    {
        $this->id = $pitchContact->id;
        $this->pitch_id = $pitchContact->pitch_id;
        /**
         * This sections dynamically sets either the team_member_id property or
         * the user_id property depending on the data coming from the model. By
         * doing it this way we avoid a redundant null value being returned.
         */
        $hasTeamMember = !is_null($pitchContact->team_member_id);
        $hasUser = !is_null($pitchContact->user_id);
        if ($hasTeamMember && !$hasUser) {
            $this->team_member_id = $pitchContact->team_member_id;
        } else if (!$hasTeamMember && $hasUser) {
            $this->user_id = $pitchContact->user_id;
        } else {
            throw new Exception();
        }
    }
}
<?php

namespace App\Resources;

use App\Models\TeamMember as TeamMemberModel;

class MaskedTeamMemberResource extends Resource
{
    public function __construct(TeamMemberModel $teamMember)
    {
        $this->id = $teamMember->id;
        $this->organisation_id = $teamMember->organisation_id;
        $this->first_name = $teamMember->first_name;
        $this->last_name = $teamMember->last_name;
        $this->job_role = $teamMember->job_role;
        $this->avatar = $teamMember->avatar;
        $this->twitter = $teamMember->twitter;
        $this->facebook = $teamMember->facebook;
        $this->linkedin = $teamMember->linkedin;
        $this->instagram = $teamMember->instagram;
    }
}

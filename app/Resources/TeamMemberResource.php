<?php

namespace App\Resources;

use App\Models\TeamMember as TeamMemberModel;

class TeamMemberResource extends Resource
{
    public function __construct(TeamMemberModel $teamMember)
    {
        $this->id = $teamMember->id;
        $this->organisation_id = $teamMember->organisation_id;
        $this->first_name = $teamMember->first_name;
        $this->last_name = $teamMember->last_name;
        $this->email_address = $teamMember->email_address;
        $this->phone_number = $teamMember->phone_number;
        $this->job_role = $teamMember->job_role;
        $this->avatar = $teamMember->avatar;
        $this->twitter = $teamMember->twitter;
        $this->facebook = $teamMember->facebook;
        $this->linkedin = $teamMember->linkedin;
        $this->instagram = $teamMember->instagram;
    }
}

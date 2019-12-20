<?php

namespace App\Resources;

use App\Models\TeamMember as TeamMemberModel;

class TeamMemberResource extends Resource
{
    public $id = null;
    public $organisation_id = null;
    public $first_name = null;
    public $last_name = null;
    public $email_address = null;
    public $job_role = null;
    public $twitter = null;
    public $facebook = null;
    public $linkedin = null;
    public $instagram = null;
    public $avatar = null;
    public $phone_number = null;

    public function __construct(TeamMemberModel $teamMember)
    {
        $this->id = $teamMember->id;
        $this->organisation_id = $teamMember->organisation_id;
        $this->first_name = $teamMember->first_name;
        $this->last_name = $teamMember->last_name;
        $this->email_address = $teamMember->email_address;
        $this->job_role = $teamMember->job_role;
        $this->twitter = $teamMember->twitter;
        $this->facebook = $teamMember->facebook;
        $this->linkedin = $teamMember->linkedin;
        $this->instagram = $teamMember->instagram;
        $this->avatar = $teamMember->avatar;
        $this->phone_number = $teamMember->phone_number;
    }
}
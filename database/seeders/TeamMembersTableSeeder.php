<?php

namespace Database\Seeders;

use App\Models\TeamMember as TeamMemberModel;
use Illuminate\Database\Seeder;

class TeamMembersTableSeeder extends Seeder
{
    public function run()
    {
        $teamMember = new TeamMemberModel();
        $teamMember->id = 1;
        $teamMember->organisation_id = 1;
        $teamMember->first_name = 'Tom';
        $teamMember->last_name = 'Smith';
        $teamMember->job_role = 'Manager';
        $teamMember->facebook = 'https://www.facebook.com/foo';
        $teamMember->twitter = 'https://www.twitter.com/bar';
        $teamMember->saveOrFail();

        $teamMember = new TeamMemberModel();
        $teamMember->id = 2;
        $teamMember->organisation_id = 1;
        $teamMember->first_name = 'Joseph';
        $teamMember->last_name = 'Smith';
        $teamMember->job_role = 'Manager';
        $teamMember->instagram = 'https://www.instagram.com/baz';
        $teamMember->linkedin = 'https://www.linkedin.com/qux';
        $teamMember->saveOrFail();

        $teamMember = new TeamMemberModel();
        $teamMember->id = 3;
        $teamMember->organisation_id = 2;
        $teamMember->first_name = 'Anthony';
        $teamMember->last_name = 'Smith';
        $teamMember->job_role = 'Manager';
        $teamMember->instagram = 'https://www.instagram.com/foo';
        $teamMember->linkedin = 'https://www.linkedin.com/bar';
        $teamMember->saveOrFail();
    }
}
